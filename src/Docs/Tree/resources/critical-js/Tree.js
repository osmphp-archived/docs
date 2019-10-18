import ViewModel from 'Osm_Framework_Js/ViewModel';
import Item from "./Item";
import expandCollapseState from './vars/expandCollapseState';
import addClass from "Osm_Framework_Js/addClass";
import removeClass from "Osm_Framework_Js/removeClass";

export default class Tree extends ViewModel {
    onAttach() {
        super.onAttach();
        this.removeLinkToCurrentUrl();
        this.restoreExpandCollapseState();
    }

    get contents_button_element() {
        if (!this.model.contents_button) {
            return null;
        }

        return document.getElementById(this.model.contents_button);
    }

    get drawer_element() {
        if (!this.model.drawer) {
            return null;
        }

        return document.getElementById(this.model.drawer);
    }

    get mobile() {
        return this.model.hide_if_window_width_less_than
            ? window.innerWidth < this.model.hide_if_window_width_less_than
            : null;
    }

    restoreExpandCollapseState() {
        Array.prototype.forEach.call(this.element.querySelectorAll('.tree__item'), itemElement => {
            let item = new Item(itemElement);

            if (!item.is_collapsed) {
                return;
            }

            if (this.model.expand_collapse_state[item.url] || expandCollapseState.getItemState(item.url)) {
                item.expand();
            }
        });
    }

    removeLinkToCurrentUrl() {
        let titleElement = this.element.querySelector(`.tree__item-title[href="${location.href}"]`);
        if (!titleElement) {
            return;
        }

        titleElement.outerHTML = `<span class="tree__item-title -current" data-url="${location.href}">${document.title}</span>`;
    }

    onResize() {
        if (this.drawer_opened) {
            if (!this.mobile) {
                this.drawer_was_opened_on_mobile = true;
                this.closeDrawer();

                sessionStorage.removeItem('hide_book_page_contents_on_desktop');
                this.show();
            }

            return;
        }

        if (this.mobile) {
            if (this.drawer_was_opened_on_mobile) {
                delete this.drawer_was_opened_on_mobile;
                this.openInDrawer();
            }
            else {
                this.hide();
            }

            return;
        }

        if (sessionStorage.getItem('hide_book_page_contents_on_desktop')) {
            this.hide();
        }
        else {
            this.show();
        }
    }

    hide() {
        addClass(this.element, '-hidden');
        if (this.contents_button_element) {
            removeClass(this.contents_button_element, '-filled');
        }
    }

    show() {
        removeClass(this.element, '-hidden');
        if (this.contents_button_element) {
            addClass(this.contents_button_element, '-filled');
        }
    }

    openInDrawer() {
        let children = Array.prototype.slice.call(this.element.parentNode.children);
        this.parent_element = this.element.parentNode;
        this.index_in_parent_element = children.indexOf(this.element);
        this.drawer_opened = true;

        this.drawer_element.appendChild(this.element);

        removeClass(this.drawer_element, '-hidden');
        this.show();
    }

    closeDrawer() {
        let children = Array.prototype.slice.call(this.parent_element.children);
        if (this.index_in_parent_element >= children.length) {
            this.parent_element.appendChild(this.element);
        }
        else {
            this.parent_element.insertBefore(this.element, children[this.index_in_parent_element]);
        }

        addClass(this.drawer_element, '-hidden');
        this.hide();

        delete this.parent_element;
        delete this.index_in_parent_element;
        delete this.drawer_opened;
    }
}