import isString from "Manadev_Framework_Js/isString";
import parseUrl from "Manadev_Framework_Js/parseUrl";
import config from "Manadev_Framework_Js/vars/config";

export default class Page {
    constructor(url) {
        if (isString(url)) {
            url = parseUrl(url);
        }
        this.url = url;
    }

    get name() {
        if (this._name === undefined) {
            this._name = this.url.pathname.substr(config.book.path.length);
            if (this._name !== '/') {
                this._name = this._name.substr(0, this._name.length - config.book.suffix.length);
            }
        }
        return this._name;
    }
};