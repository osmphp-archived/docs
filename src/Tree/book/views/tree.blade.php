<?php
/* @var \Osm\Docs\Tree\Views\Tree $view */
?>
<nav class="tree {{ $view->modifier }}" id="{{ $view->id_ }}">
    <h4>{{ osm_t("Contents") }}</h4>
    <?php $view->renderer->page = $view->book->getPage('/'); ?>
    @include ($view->renderer)
    <?php $view->renderer->page = null; ?>
</nav>
{!! $view->view_model_script !!}