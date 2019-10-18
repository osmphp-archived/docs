import Controller from "Osm_Framework_Js/Controller";
import macaw from "Osm_Framework_Js/vars/macaw";
import Tree from "Osm_Docs_Tree/Tree";

export default class BookPage extends Controller {
    get events() {
        return Object.assign({}, super.events, {
            // commands in breadcrumbs
            'click #breadcrumbs__menu__contents': 'onContents',
            'click #_body_end_book-page-tree-drawer .tree-drawer__close': 'onContentsDrawerClose',
        });
    }

    get contents() {
        return macaw.get(document.getElementById('tree'), Tree);
    }

    onContents() {
        this.contents.toggle();
    }

    onContentsDrawerClose() {
        this.contents.closeDrawer();
    }
};