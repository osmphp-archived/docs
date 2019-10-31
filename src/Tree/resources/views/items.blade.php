<?php
/* @var \Osm\Docs\Tree\Views\Items $view */

use Osm\Docs\Docs\Page;

$renderedPage = $view->page;
?>
<ul class="tree__items -level{{ $renderedPage->level }} @if($renderedPage->level) -collapsed @endif">
    @foreach($view->page->child_pages as $page)
        <?php /* @var Page $page */?>
        <?php $view->page = $page; ?>
        <?php $hasChildren = count($view->page->child_pages) > 0; ?>

        <li class="tree__item -level{{ $renderedPage->level }}">
            <div class="tree__item-header -level{{ $renderedPage->level }}">
                <span class="tree__item-icon @if($hasChildren) -clickable @endif">
                    <i class="icon @if($hasChildren) -expand @endif"></i>
                </span>

                <a href="{{ $view->page->url }}" class="tree__item-title">
                    {{ $view->page->title }}
                </a>
            </div>

            @if ($hasChildren)
                @include ($view)
            @endif
        </li>

    @endforeach
</ul>
<?php $view->page = $renderedPage; ?>