<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/12/16
 * Time: 11:27
 */

namespace myConf\Libraries;

class Output
{
    /**
     * @param string $template_name
     * @param array $parameters
     * @throws \myConf\Exceptions\TemplateNotFoundException
     * @throws \myConf\Exceptions\TemplateParseException
     */
    public static function return_template(string $template_name, array $parameters = array()) : void
    {
        $target_template_file = TEMPLATE_BASE_PATH . $template_name;
        //载入模板
        $template = NativeTpl::load($target_template_file, ENVIRONMENT !== 'production');
        //分离变量
        foreach ($parameters as $key => $val) {
            $$key = $val;
        }
        //加载变量显示输出
        include($template);
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