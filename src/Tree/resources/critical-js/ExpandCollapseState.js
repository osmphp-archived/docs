export default class ExpandCollapseState {
    get state() {
        if (this._state === undefined) {
            this._state = JSON.parse(sessionStorage.getItem('tree_expand_collapse_state') || '{}');
        }
        
        return this._state;
    }
    
    getItemState(itemUrl) {
        return this.state[itemUrl];
    }
    
    saveItemAsCollapsed(itemUrl) {
        delete this.state[itemUrl];
        sessionStorage.setItem('tree_expand_collapse_state', JSON.stringify(this.state));
    }
    
    saveItemAsExpanded(itemUrl) {
        this.state[itemUrl] = true;
        sessionStorage.setItem('tree_expand_collapse_state', JSON.stringify(this.state));
    }
}