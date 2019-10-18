import getClassSuffix from "Osm_Framework_Js/getClassSuffix";
import hasClass from "Osm_Framework_Js/hasClass";
import firstParentElement from "Osm_Framework_Js/firstParentElement";
import removeClass from "Osm_Framework_Js/removeClass";
import addClass from "Osm_Framework_Js/addClass";

export default class Item {
    constructor(itemElement) {
        if (!hasClass(itemElement, 'tree__item')) {
            itemElement = firstParentElement(itemElement, element => hasClass(element, 'tree__item'));
        }
        this.item_element = itemElement;
    }

    get level() {
        if (this._level === undefined) {
            this._level = parseInt(getClassSuffix(this.item_element, '-level'));
        }

        return this._level;
    }

    get header_element() {
        if (this._header_element === undefined) {
            this._header_element = this.item_element.querySelector(`.tree__item-header.-level${this.level}`);
        }

        return this._header_element;
    }

    get icon_element() {
        if (this._icon_element === undefined) {
            this._icon_element = this.header_element.querySelector('.tree__item-icon');
        }

        return this._icon_element;
    }

    get symbol_element() {
        if (this._symbol_element === undefined) {
            this._symbol_element = this.icon_element.querySelector('.icon');
        }

        return this._symbol_element;
    }

    get title_element() {
        if (this._title_element === undefined) {
            this._title_element = this.header_element.querySelector('.tree__item-title');
        }

        return this._title_element;
    }

    get menu_element() {
        if (this._menu_element === undefined) {
            this._menu_element = this.header_element.querySelector('.tree__item-menu');
        }

        return this._menu_element;
    }

    get child_items_element() {
        if (this._child_items_element === undefined) {
            this._child_items_element = this.item_element.querySelector(`.tree__items.-level${this.level + 1}`);
        }

        return this._child_items_element;
    }

    get url() {
        if (this._url === undefined) {
            this._url = this.title_element.getAttribute('href') || this.title_element.getAttribute('data-url');
        }

        return this._url;
    }

    get title() {
        if (this._title === undefined) {
            this._title = this.title_element.innerHTML.trim()
        }

        return this._title;
    }

    get is_collapsed() {
        return hasClass(this.symbol_element, '-expand');
    }

    get is_expanded() {
        return hasClass(this.symbol_element, '-collapse');
    }

    collapse() {
        removeClass(this.symbol_element, '-collapse');
        addClass(this.symbol_element, '-expand');
        addClass(this.child_items_element, '-collapsed');
    }

    expand() {
        removeClass(this.symbol_element, '-expand');
        addClass(this.symbol_element, '-collapse');
        removeClass(this.child_items_element, '-collapsed');
    }
}