<div class="video"></div>

<div class="main">

<h1 id="title"><a href="{$site_url}">{$site_title}</a></h1>

{include file="$file_path/themes/default/header_text.tpl"}

<br />

<div class="contents">

{$info_data}

<br />
{$item_count} items
{$pager_data}

<div class="article">
{$item_data}
</div>

</div>

{$pager_data}

{include file="$file_path/themes/default/footer_text.tpl"}

</div>
