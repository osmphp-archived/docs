<?php
/* @var \Manadev\Docs\Docs\Views\Breadcrumbs $view */
?>
@if (count($view->file->parent_pages))
    <nav class="breadcrumbs">
        @foreach ($view->file->parent_pages as $url => $title)
            <a href="{{ $url }}">{{ $title }}</a>
            @if (!$loop->last) &gt; @endif
        @endforeach
    </nav>
@endif
