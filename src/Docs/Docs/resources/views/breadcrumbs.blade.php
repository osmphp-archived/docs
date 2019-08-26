<?php
/* @var \Osm\Docs\Docs\Views\Breadcrumbs $view */
?>
@if (count($view->page->parent_pages) || count($view->menu->items_))
    <div class="breadcrumbs">
        <nav class="breadcrumbs__items">
            @foreach ($view->page->parent_pages as $page)
                <a href="{{ $page->url }}">{{ $page->title }}</a>
                @if (!$loop->last) &gt; @endif
            @endforeach
        </nav>

        @if (count($view->menu->items_))
            <div class="breadcrumbs__menu">
                @include ($view->menu)
            </div>
        @endif
    </div>
@endif
