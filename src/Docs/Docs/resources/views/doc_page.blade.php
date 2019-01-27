<?php
/* @var \Manadev\Docs\Docs\Views\DocPage $view */
?>
@if (count($view->file->parent_pages))
    <nav>
        @foreach ($view->file->parent_pages as $url => $title)
            <a href="{{ $url }}">{{ $title }}</a>
            @if (!$loop->last) &gt; @endif
        @endforeach
    </nav>
@endif

{!! $view->file->html !!}
