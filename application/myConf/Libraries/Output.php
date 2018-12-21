<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/16
 * Time: 11:27
 */

namespace myConf\Libraries;


/**
 * Smarty trimwhitespace outputfilter plugin
 * Trim unnecessary whitespace from HTML markup.
 *
 * @author Rodney Rehm
 *
 * @param string $source input string
 *
 * @return string filtered output
 * @todo   substr_replace() is not overloaded by mbstring.func_overload - so this function might fail!
 */
function smarty_outputfilter_trimwhitespace($source)
{
    $store = array();
    $_store = 0;
    $_offset = 0;
    // Unify Line-Breaks to \n
    $source = preg_replace('/\015\012|\015|\012/', "\n", $source);
    // capture Internet Explorer and KnockoutJS Conditional Comments
    if (preg_match_all(
        '#<!--((\[[^\]]+\]>.*?<!\[[^\]]+\])|(\s*/?ko\s+.+))-->#is',
        $source,
        $matches,
        PREG_OFFSET_CAPTURE | PREG_SET_ORDER
    )
    ) {
        foreach ($matches as $match) {
            $store[] = $match[0][0];
            $_length = strlen($match[0][0]);
            $replace = '@!@SMARTY:' . $_store . ':SMARTY@!@';
            $source = substr_replace($source, $replace, $match[0][1] - $_offset, $_length);
            $_offset += $_length - strlen($replace);
            $_store++;
        }
    }
    // Strip all HTML-Comments
    // yes, even the ones in <script> - see http://stackoverflow.com/a/808850/515124
    $source = preg_replace('#<!--.*?-->#ms', '', $source);
    // capture html elements not to be messed with
    $_offset = 0;
    if (preg_match_all(
        '#(<script[^>]*>.*?</script[^>]*>)|(<textarea[^>]*>.*?</textarea[^>]*>)|(<pre[^>]*>.*?</pre[^>]*>)#is',
        $source,
        $matches,
        PREG_OFFSET_CAPTURE | PREG_SET_ORDER
    )
    ) {
        foreach ($matches as $match) {
            $store[] = $match[0][0];
            $_length = strlen($match[0][0]);
            $replace = '@!@SMARTY:' . $_store . ':SMARTY@!@';
            $source = substr_replace($source, $replace, $match[0][1] - $_offset, $_length);
            $_offset += $_length - strlen($replace);
            $_store++;
        }
    }
    $expressions = array(// replace multiple spaces between tags by a single space
        // can't remove them entirely, becaue that might break poorly implemented CSS display:inline-block elements
        '#(:SMARTY@!@|>)\s+(?=@!@SMARTY:|<)#s' => '\1 \2',
        // remove spaces between attributes (but not in attribute values!)
        '#(([a-z0-9]\s*=\s*("[^"]*?")|(\'[^\']*?\'))|<[a-z0-9_]+)\s+([a-z/>])#is' => '\1 \5',
        // note: for some very weird reason trim() seems to remove spaces inside attributes.
        // maybe a \0 byte or something is interfering?
        '#^\s+<#Ss' => '<',
        '#>\s+$#Ss' => '>',
    );
    $source = preg_replace(array_keys($expressions), array_values($expressions), $source);
    // note: for some very weird reason trim() seems to remove spaces inside attributes.
    // maybe a \0 byte or something is interfering?
    // $source = trim( $source );
    $_offset = 0;
    if (preg_match_all('#@!@SMARTY:([0-9]+):SMARTY@!@#is', $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $_length = strlen($match[0][0]);
            $replace = $store[$match[1][0]];
            $source = substr_replace($source, $replace, $match[0][1] + $_offset, $_length);
            $_offset += strlen($replace) - $_length;
            $_store++;
        }
    }
    return $source;
}

class Output
{

    /**
     * @param string $template_name
     * @param array $parameters
     * @param string $engine
     * @throws \myConf\Exceptions\TemplateNotFoundException
     * @throws \myConf\Exceptions\TemplateParseException
     */
    public static function return_template(string $template_name, array $parameters = array(), string $engine = 'native'): void
    {
        $target_template_file = TEMPLATE_BASE_PATH . $template_name;
        switch ($engine) {
            //自带引擎
            default :
                {
                    //载入模板
                    $template = NativeTpl::load($target_template_file, ENVIRONMENT !== 'production');
                    //分离变量
                    foreach ($parameters as $key => $val) {
                        $$key = $val;
                    }
                    //加载变量显示输出
                    include($template);
                }
        }
    }

    public static function return_json(array $parameters = array()): void
    {
        self::_get_benchmark_info();
        header('Content-Type:application/json;charset=utf-8');
        echo json_encode($parameters);
    }

    private static function _get_benchmark_info(): array
    {
        global $start_ts;
        global $end_ts;
        $end_ts = (int)microtime(true);
        $time_str = sprintf("%.3f", ($end_ts - $start_ts) / 1000);
        $mem = function_exists('memory_get_peak_usage') ? sprintf("%.2f", memory_get_peak_usage() / 1048576) : 'Unknown';
        $CI = &get_instance();
        $sql = $CI->db->total_queries();
        log_message('INFO', "$time_str sec, $mem MiB, $sql queries");
        return array('sql_queries' => $sql, 'elapsed_time' => $time_str, 'mem_usage' => $mem);
    }

    public static function clear_compiled_template(): void
    {
        File::clear_directory_with_files(TEMPLATE_COMPILED_PATH, false);
        return;
    }
}