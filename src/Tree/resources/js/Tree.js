import Controller from 'Osm_Framework_Js/Controller';
import Item from './Item';
import expandCollapseState from './vars/expandCollapseState';

export default class Tree extends Controller {
    get events() {
        return Object.assign({}, super.events, {
            'click .tree__item-icon': 'onIconClick'
        });
    }

    onIconClick(e) {
        let item = new Item(e.currentTarget);

        if (item.is_collapsed) {
            item.expand();
            expandCollapseState.saveItemAsExpanded(item.url);
        }
        else if (item.is_expanded) {
            item.collapse();
            expandCollapseState.saveItemAsCollapsed(item.url);

            Array.prototype.forEach.call(item.item_element.querySelectorAll('.tree__item'), element => {
                let item = new Item(element);

                if (item.is_expanded) {
                    item.collapse();
                    expandCollapseState.saveItemAsCollapsed(item.url);
                }
            });
        }
    }

    toggle() {
        if (this.view_model.mobile) {
            if (this.view_model.drawer_opened) {
                this.view_model.closeDrawer();
            }
            else {
                this.view_model.openInDrawer();
            }
        }
        else {
            if (sessionStorage.getItem('hide_book_page_contents_on_desktop')) {
                sessionStorage.removeItem('hide_book_page_contents_on_desktop');
                this.view_model.show();
            }
            else {
                sessionStorage.setItem('hide_book_page_contents_on_desktop', 'true');
                delete this.view_model.drawer_was_opened_on_mobile;
                this.view_model.hide();
            }
        }
    }

    closeDrawer() {
        this.view_model.closeDrawer();
    }
};