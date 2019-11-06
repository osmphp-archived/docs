import isString from "Osm_Framework_Js/isString";
import parseUrl from "Osm_Framework_Js/parseUrl";
import config from "Osm_Framework_Js/vars/config";

export default class Page {
    constructor(url) {
        if (isString(url)) {
            url = parseUrl(url);
        }
        this.url = url;
    }

    get name() {
        if (this._name === undefined) {
            this._name = this.url.pathname.substr(config.base_url);
            if (this._name !== '/') {
                this._name = this._name.substr(0, this._name.length - config.book.suffix.length);
            }
        }
        return this._name;
    }
};