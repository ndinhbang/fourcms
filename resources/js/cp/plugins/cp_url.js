import { cp_url } from "../bootstrap/globals";

export default {

    install(Vue, options) {

        Vue.prototype.cp_url = function(url) {
            return cp_url(url);
        };

    }

};
