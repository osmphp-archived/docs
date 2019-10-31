import merge from 'Osm_Framework_Js/merge';
import Tree from './Tree';
import Item from './Item';
import expandCollapseState from './vars/expandCollapseState';

merge(window, {
    Osm_Docs_Tree: { Tree, Item, vars: {expandCollapseState} }
});