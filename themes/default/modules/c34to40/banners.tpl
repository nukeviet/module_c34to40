<!-- BEGIN: main -->

<b>Hướng dẫn thực hiện:</b>
<br>
<br>
- Dùng phpmyadmin, hoặc các công cụ khác để import CSDL site NukeViet 3.4 vào cùng một CSDL với site chạy NukeViet 4. Chú ý trước khi import cần chạy qua tool này:
<pre>
    <code class="language-php">&#x3C;?php

function unfixdb($value)
{
    $value = preg_replace(array(
        &#x22;/(se)\-(lect)/i&#x22;,
        &#x22;/(uni)\-(on)/i&#x22;,
        &#x22;/(con)\-(cat)/i&#x22;,
        &#x22;/(c)\-(har)/i&#x22;,
        &#x22;/(out)\-(file)/i&#x22;,
        &#x22;/(al)\-(ter)/i&#x22;,
        &#x22;/(in)\-(sert)/i&#x22;,
        &#x22;/(d)\-(rop)/i&#x22;,
        &#x22;/(f)\-(rom)/i&#x22;,
        &#x22;/(whe)\-(re)/i&#x22;,
        &#x22;/(up)\-(date)/i&#x22;,
        &#x22;/(de)\-(lete)/i&#x22;,
        &#x22;/(cre)\-(ate)/i&#x22;), &#x22;$1$2&#x22;, $value);
    return $value;
}

$contents = file_get_contents(&#x27;mysql.sql&#x27;);
$contents = unfixdb($contents);

file_put_contents(&#x27;mysql_fixed.sql&#x27;, $contents, LOCK_EX);

echo(&#x27;OK&#x27;);</code>
</pre>
- Copy thư mục upload của module banners cũ, Sang thư mục của module banners đang sử dụng của NukeViet 4
<br>
<br>
- Sử dụng chức năng để chuyển đổi.
<br>
<br>

<!-- BEGIN: error_modulenv3 -->
<div class="alert alert-danger">Lỗi: Chưa có CSDL NukeViet v3 được import.</div>
<!-- END: error_modulenv3 -->

<form class="form-horizontal" action="{NV_BASE_SITEURL}index.php?{NV_LANG_VARIABLE}={NV_LANG_DATA}&amp;{NV_NAME_VARIABLE}={MODULE_NAME}&amp;{NV_OP_VARIABLE}={OP}" method="post">
    <input type="hidden" name="save"  value="1" />
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <input name="import" type="submit" value="Thực hiện nâng cấp" class="btn btn-primary">
        </div>
    </div>
</form>

<!-- END: main -->
