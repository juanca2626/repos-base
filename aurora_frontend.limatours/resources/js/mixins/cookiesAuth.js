import Cookies from "js-cookie";

export const cookiesAuth = {
    data: () => {
        return {
            TokenKey: window.tokenKey,
            UserKey: window.userKey,
            Domain: { domain: window.domain },
            verifyAuth: false
        }
    },
    methods: {
        removeCookies() {
            Cookies.remove(this.TokenKey, this.Domain);
            Cookies.remove(this.UserKey, this.Domain);
        },
        getDataCookieTokenAndUser() {
            return {
                token: Cookies.get(this.TokenKey),
                userId: Cookies.get(this.UserKey),
            };
        },
        verifyToken() {
            const cookies = this.getDataCookieTokenAndUser();
            if (cookies.token && cookies.userId) {
                this.verifyAuth = true;
                return this.verifyAuth;
            } else {
                this.verifyAuth = false
                return this.verifyAuth
            }
        },
    },
};
