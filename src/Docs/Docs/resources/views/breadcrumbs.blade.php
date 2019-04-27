<?php
/* @var \Manadev\Docs\Docs\Views\Breadcrumbs $view */
?>
@if (count($view->page->parent_pages) || count($view->menu->items_))
    <div class="breadcrumbs">
        <nav class="breadcrumbs__items">
            @foreach ($view->page->parent_pages as $url => $title)
                <a href="{{ $url }}">{{ $title }}</a>
                @if (!$loop->last) &gt; @endif
            @endforeach
        </nav>

        @if (count($view->menu->items_))
            @include ($view->menu)
        @endif
    </div>
@endif
