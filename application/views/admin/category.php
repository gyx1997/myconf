<?php
/**
 * Created by PhpStorm.
 * User: 52297
 * Date: 2018/10/20
 * Time: 20:36
 */

defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div style="width: 100%; margin: 15px;">
    <div style="padding-left:35px;">
        <div style="padding:5px;"></div>
        <div style="padding:5px;"></div>
        <form role="form" action="/admin/category/add-cat/" method="post" enctype="multipart/form-data"
              onkeydown="if(event.keyCode==13){return false;}">
            <input type="hidden" name="<?php echo $csrf_name; ?>" value="<?php echo $csrf_hash; ?>"/>
            <div class="form-inline">
                <label>
                    栏目名&nbsp;&nbsp;&nbsp;
                    <input class="form-control" type="text" name="category_name_text" id="category_name_text"/>
                </label>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <label>
                    类型&nbsp;&nbsp;&nbsp;
                    <select disabled="disabled" name="category_type_id" class="form-control">
                        <option value="0" selected="selected">只包含一篇文档</option>
                        <option value="1">多篇文档显示的列表</option>
                    </select>
                </label>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <button type="submit" class="btn btn-success">添加一个栏目</button>
            </div>
            <div style="padding: 5px;"></div>
            <div class="alert alert-info" style="padding: 5px;">目前只能使用只包含单一文档的栏目。</div>
            <div style="padding-top: 5px; padding-bottom: 5px;">
                <table class="table table-hover">
                    <thead>
                    <tr>
                        <th>栏目标题</th>
                        <th>栏目类型</th>
                        <th></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($category_list as $cat) { ?>
                        <tr>
                            <form id="category_<?php echo $cat['category_id']; ?>" role="form"
                                  action="/admin/category/mod-cat/<?php echo $cat['category_id'] ?>/" method="post"
                                  enctype="multipart/form-data">
                                <input type="hidden" name="<?php echo $csrf_name; ?>"
                                       value="<?php echo $csrf_hash; ?>"/>
                                <td>
                                    <div class="form-inline">
                                        <input name="category_name_text" id="category_name_text" type="text"
                                               class="form-control" value="<?php echo $cat['category_title']; ?>"/>
                                        &nbsp;&nbsp;
                                        <button type="submit" class="btn"
                                                onclick="window.location.href='/admin/category/mod-cat/<?php echo $cat['category_id']; ?>/'">
                                            修改
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($cat['category_type'] == 0) { ?>
                                        <select disabled="disabled" name="category_type_id" class="form-control">
                                            <option value="0" selected="selected">只包含一篇文档</option>
                                            <option value="1">多篇文档显示的列表</option>
                                        </select>
                                    <?php } else { ?>
                                        <select disabled="disabled" name="category_type_id" class="form-control">
                                            <option value="0">只包含一篇文档</option>
                                            <option value="1" selected="selected">多篇文档显示的列表</option>
                                        </select>
                                    <?php } ?>
                                </td>
                                <td>
                                    <button type="button" class="btn"
                                            onclick="window.location.href='/admin/category/up-cat/<?php echo $cat['category_id']; ?>/'">
                                        上移
                                    </button>
                                    <button type="button" class="btn"
                                            onclick="window.location.href='/admin/category/down-cat/<?php echo $cat['category_id']; ?>/'">
                                        下移
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn"
                                            onclick="window.location.href='/admin/category/del-cat/<?php echo $cat['category_id']; ?>/'">
                                        删除
                                    </button>
                                    <?php if ($cat['category_type'] == 0) { ?>
                                        <button type="button" class="btn"
                                                onclick="window.location.href='/admin/category/mod-doc/<?php echo $cat['category_id']; ?>/'">
                                            编辑文章
                                        </button>
                                    <?php } else { ?>
                                        <button type="button" class="btn"
                                                onclick="window.location.href='/admin/category/mod-doc-list/<?php echo $cat['category_id']; ?>/'">
                                            编辑文章列表
                                        </button>
                                    <?php } ?>
                                </td>
                            </form>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

