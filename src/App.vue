<script setup>
import { provide } from 'vue'
import LoginDlg from '@/components/LoginDlg.vue'
import ConfirmDlg from '@/components/ConfirmDlg.vue'
import PasswordDlg from '@/components/PasswordDlg.vue'
import { postURL, bracket } from '@/utils.js'
import 'vue-router'
 </script>

<script>
export default {
    components: { LoginDlg, ConfirmDlg },
    expose: ['confirm', 'notify', 'post', 'accessible', 'resetPassword', 'moved'],
    provide: function() {
        return { 
            loadMenu: this.loadMenu,
            post: this.post,
            notify: this.notify,
            confirm: this.confirm,
            accessible: this.accessible
        };
    },
    data() {
        return {
            showing: {
                drawer: false,
                notice: false,
            },

            notice: {
                message: '',
                color:'primary'
            },

            credential: {
                username: '',
                fullname: "",
                access: ''
            },

            confirmDlg: null
        }
    },

    computed: {
        loggedIn() {
            return this.credential.username != '';
        },

        initial() {
            var tokens = this.credential.fullname.split(/\s+/)
            if (tokens.length >= 2) {
                return tokens[0].charAt(0).toUpperCase()+tokens[tokens.length-1].charAt(0).toUpperCase()
            }
            else {
                return tokens[0].substring(0,2).toUpperCase()
            }
        }
    },

    created() {
        this.$router.afterEach((to, from) => {
            this.moved();
        })
    },

    mounted() {
        this.sayHello()
    },

    methods: {
        raise(msg) {
            throw {status:'error', message: msg};
        },

        async post(url, args, tries) {
            if (tries === undefined) {
                tries = 5;
            }

            var first_ex = null;
            while (tries > 0) {
                --tries;
                try {
                    return await postURL(url, args);
                }
                catch (ex) {
                    if (!first_ex) {
                        first_ex = ex;
                    }

                    if (ex.status != 'unauthorized' || !await this.signIn(ex.message)) {
                        break;
                    }
                    // else: login succeded, try re-posting
                }
            }
            throw first_ex;
        },

        userLoggedIn(login) {
            this.credential.username = login.username;
            this.credential.fullname = login.fullname;
            this.credential.access = login.access;
            this.notify(login.message);
        },

        userLoggedOut(message) {
            this.credential.username = '';
            this.credential.fullname = '';
            this.credential.access = '';
            if (message) {
                this.notify(message);
            }
        },

        notify(message, color) {
            this.notice.color = color || 'blue';
            console.log(message);
            this.notice.message = message;
            this.showing.notify = true;
        },

        accessible(priv) {
            console.log(priv, this.credential.access);
            return this.credential.access.split(',').indexOf(priv) != -1;
        },

        async confirm(headline, prompt, mode) {
            return await this.$refs.confirmDlg.open(mode || 'YNC', headline, prompt);
        },

        async sayHello() {
            try {
                var resp = await postURL('hello.php', {});
                this.userLoggedIn(resp);
            }
            catch (ex) {
                this.signIn();
            }
        },

        async signOut() {
            try {
                var ans = await this.confirm('Sign Out', "Sign out account "+bracket(this.credential.username)+"?", "YC");
                if (ans == 'yes') {
                    var resp = await postURL('logout.php', {});
                    this.userLoggedOut(resp.message);
                }
            }
            catch (ex) {
                console.log(ex);
            }
        },

        async signIn(message) {
            var resp = await this.$refs.loginDlg.open(message);
            console.log('sign in', resp);
            if (resp) {
                this.userLoggedIn(resp);
                return true;
            }
            return false;
        },

        async resetPassword(username, require_old_password) {
            await this.$refs.passwordDlg.open(username, require_old_password);
        },

        async changeMyPassword() {
            await this.resetPassword(this.credential.username, !this.accessible('admin'));
        },

        clearMenu() {
            var menubar = this.$refs.appmenu.$el;
            while (menubar.firstChild) {
                menubar.removeChild(menubar.firstChild);
            }
        },

        loadMenu(menu) {
            //maybe don't clear out menu if moving into a nexted route???
            this.clearMenu();
            var menubar = this.$refs.appmenu.$el;
            menubar.appendChild(menu);
        },

        moved(to, from) {
            this.showing.drawer = false;
            this.clearMenu();
        }
    },


}
</script>

<template>
    <v-app>
        <v-app-bar density="compact">
        <v-app-bar-nav-icon @click="showing.drawer=!showing.drawer"></v-app-bar-nav-icon>
        <v-container ref="appmenu" id="appmenu" style="display:flex; flex-wrap:nowrap;"></v-container>
        <v-spacer></v-spacer>
        <v-menu offset-y>
            <template v-slot:activator="{ props }">
            <v-btn class="login-avatar" :class="{verified:loggedIn}" v-bind="props" icon size="x-small">
                <span v-if="loggedIn" class="name-initial">{{  initial }}</span><v-icon v-else size="x-large">mdi-account</v-icon>
            </v-btn>
            </template>
            <v-list>
            <span v-if="loggedIn">
                <v-list-item prepend-icon="mdi-account">{{ credential.fullname }}</v-list-item>
                <v-list-item prepend-icon="mdi-lock-reset" @click="changeMyPassword"
                    >Reset Password</v-list-item>
                <v-list-item prepend-icon="mdi-logout" @click="signOut">Sign Out</v-list-item>
            </span>
            <v-list-item v-else prepend-icon="mdi-login" @click="signIn()">Sign In</v-list-item>
            </v-list>
        </v-menu>
        </v-app-bar>

        <v-navigation-drawer :permanent="showing.drawer" rail expand-on-hover>
            <v-list class="main-menu" density="compact" nav>
                <router-link to="/settings"><v-list-item prepend-icon="mdi-chair-school" title="Registration Settings"></v-list-item></router-link>
                <router-link to="/users"><v-list-item prepend-icon="mdi-account-cog" title="User Accounts"></v-list-item></router-link>
                <router-link to="/student"><v-list-item prepend-icon="mdi-account-school" title="Student Accounts"></v-list-item></router-link>
                <a href="registration-letter.php?logo=yes&period=gl" target="_blank"><v-list-item prepend-icon="mdi-email-mark-as-unread" title="Re-registration" subtitle="Reminder Letter"></v-list-item></a>
            </v-list>
        </v-navigation-drawer>

        <v-main>
            <router-view></router-view>
            <LoginDlg ref="loginDlg"></LoginDlg>
            <ConfirmDlg ref="confirmDlg"></ConfirmDlg>
            <PasswordDlg ref="passwordDlg"></PasswordDlg>
            <v-snackbar v-model="showing.notify" :color="notice.color">{{ notice.message }}</v-snackbar>
        </v-main>
    </v-app>
</template>

<style>
    .login-avatar {
        background-color: #ccc!important;
        color: white;
    }

    .login-avatar.verified {
        background-color: #0a0!important;
        color: white;
    }

    .login-avatar .name-initial {
        font-size: 150%;
        font-weight: 300;
    }

    .main-menu .v-list-item__content {
        text-align: left;
    }
</style>
