<script setup>
import { FilterSet, alike, bracket } from '@/utils.js'
</script>

<script>

function noop() {}

function nobody() {
    return {
        username:'', 
        fullname:'', 
        email:'', 
        role:'', 
        access:'',
	    common:false, 
        deactivated:false
    };
}

export default {
    props:['appmenu'],
    expose: ['edited','selectUser','loadMenu','unloadMenu'],
    data() {
        return {
            filter: "",
            listing: [],
            edit: nobody(),
            orig: nobody(),
            roles: ['HT', 'Teacher', 'Admin']
        }
    },

    computed: {
        edited() { return !alike(this.edit, this.orig);}
    },

    mounted() {
        this.listUsers();
        this.loadMenu();
    },
    
    async beforeRouteLeave(to, from, next) {
        if (await this.selectUser(nobody())) {
            this.unloadMenu();
            next();
        }
        else {
            next(false);
        }
    },

    methods: {
        loadMenu() {
            var menubar = document.getElementById(this.appmenu);
            if (menubar) {
                menubar.appendChild(this.$refs.cmenu.$el)
            }
        },

        unloadMenu() {
            var menubar = document.getElementById(this.appmenu);
            if (menubar) {
                menubar.removeChild(this.$refs.cmenu.$el)
            }
        },

        closeOut(nextRoute) {
            if (nextRoute) {
                this.unloadMenu();
                nextRoute();
            }
        },

        cancelRoute(nextRoute) {
            if (nextRoute) {
                nextRoute(false);
            }
        },

        async listUsers() {
            try {
                var resp = await this.$root.post('users.php', {op: 'list'});
                Object.assign(this.orig, this.edit);
			    this.listing.splice(0);
                resp.users.forEach(user => {
                    this.listing.push(user);
                });
            }
            catch(ex) {
                this.$root.notify(ex.message, 'error');
            }
        },

        filterUsers() {
            var v = [];
		    var filter = new FilterSet(this.filter || '.', ['username','fullname']);
            this.listing.forEach(user => {
                if (filter.match(user)) {
                    v.push(user);
                }
            });
            return v;
        },

        clearUserSearch() {
            this.filter = '';
        },

        async selectUser(user) {
            if (!this.edited) {
                Object.assign(this.orig, Object.assign(this.edit, user));
                return true;
            }
            
            var ans = await this.$root.confirm('Save Account', "Save updated user's information?");
            if (ans == 'cancel') {
                return false;
            }
            else if (ans == 'no') {
                Object.assign(this.orig, Object.assign(this.edit, user));
                return true;
            }
            else if (await this.saveUser()) {
                Object.assign(this.orig, Object.assign(this.edit, user));
                return true;
            }
            else {
                return false;
            }
        },

        newUser() {
            this.selectUser(nobody());
        },

        async saveUser() {
            if (!this.edit.username || !this.edit.fullname) {
                this.$root.notify("Empty user ID/fullname");
                return false;
            }

            try {
		        var resp = await this.$root.post("users.php", { op: 'save', user: this.edit, orig: this.orig });
                var uid = this.orig.username;
                var entry = this.listing.find(u => u.username == uid);
                if (entry) {
                    Object.assign(entry, this.edit);
                }
                else {
                    this.listing.unshift(Object.assign({}, this.edit));
                }
                Object.assign(this.orig, this.edit);
                this.$root.notify(resp.message);
                return true;
            }
            catch (ex) {
                console.log(ex);
                this.$root.notify(ex.message, 'error');
                return false;
            }
        },

        async removeUser() {
            var ans = await this.$root.confirm("Remove Account", 
                "Remove Account "+bracket(this.orig.username)+"?", "OC");
            if (ans != 'yes') {
                return;
            }
            try {
                var uid = this.orig.username;
                var resp = await this.$root.post('users.php', {
                    op: 'remove', username: this.orig.username, fullname: this.orig.fullname
                });
                this.$root.notify(resp.message, 'success');
                var ndx = this.listing.findIndex(u => u.username == uid);
                if (ndx != -1) {
                    this.listing.splice(ndx , 1);
                }
                Object.assign(this.orig, Object.assign(this.edit, nobody()));
            }
            catch(ex) {
                this.$root.notify(ex.message, 'error');
            }
        },

        resetPassword() {
            if (this.$root.resetPassword(this.orig.username, !this.$root.accessible('admin'))) {
                //
            }
        }
    }
}
</script>

<template>
    <template>
        <v-container ref="cmenu" class="users-menu">
            <v-btn variant="text" prepend-icon="mdi-account-plus" @click="newUser" color="green"
                ><span class="d-none d-sm-flex">New</span></v-btn>
            <v-btn variant="text" prepend-icon="mdi-account-check" @click="saveUser" color="blue"
                :disabled="!edit.username"
                ><span class="d-none d-sm-flex">Save</span></v-btn>
            <v-btn variant="text" prepend-icon="mdi-account-remove" @click="removeUser" color="orange"
                :disabled="!orig.username"
                ><span class="d-none d-sm-flex">Remove</span></v-btn>
            <v-btn variant="text" prepend-icon="mdi-lock-reset" @click="resetPassword" color="purple"
                :disabled="!orig.username"
                ><span class="d-none d-sm-flex">Password</span></v-btn>
        </v-container>
    </template>
    <v-container ref="editor" style="display:flex; flex-flow: wrap; align-contents:flex-start;">
        <v-card class="userinfo pa-0" flat rounded="0" width="400">
            <v-card-text class="px-2 py-0">
                <v-text-field hide-details label="ID"
                            v-model.trim="edit.username"></v-text-field>
                <v-text-field hide-details label="Fullname"
                            v-model.trim="edit.fullname"></v-text-field>
                <v-text-field hide-details label="Email"
                            v-model.trim="edit.email"></v-text-field>
                <v-combobox hide-details label="Role"
                            v-model.trim="edit.role" :items="roles"></v-combobox>
                <v-text-field hide-details label="Access"
                            v-model.trim="edit.access"></v-text-field>
                <div style="display:flex; justify-content:space-between;">
                <v-checkbox hide-details label="Common"
                            v-model="edit.common"></v-checkbox>
                <v-checkbox hide-details label="Deactivated"
                            v-model="edit.deactivated"></v-checkbox>
                </div>
            </v-card-text>
        </v-card>

        <v-card class="userlist pa-0" rounded="0" width="400">
            <v-card-title  class="px-2 py-0">
                <v-text-field class="user-filter" v-model="filter" hide-details
                    prepend-icon="mdi-magnify" clearable
                ></v-text-field>
            </v-card-title>
            <v-card-text class="px-2 py-0">
                <v-list height="400">
                    <v-list-item v-for="user in filterUsers()" :key="user.username"
                        class="user" :class="{inactive:user.deactivated }"
                        @click="()=>selectUser(user)"
                        :title="user.fullname"
                    ><v-list-item-subtitle>
                        <span>{{ user.username }}</span><span>{{user.role+'/'+user.access }}</span>
                    </v-list-item-subtitle>
                    </v-list-item>
                </v-list>
            </v-card-text>
        </v-card>
    </v-container>

</template>

<style>
    .user.v-list-item {
        text-align: left;
    }

    .user.v-list-item .v-list-item-title {
        color: #25a;
    }

    .user.v-list-item.inactive .v-list-item-title {
        text-decoration: line-through;
    }

    .user.v-list-item .v-list-item-subtitle {
        display: flex;
        justify-content: space-between;
    }
    .user.v-list-item .v-list-item-subtitle SPAN {
        display: inline-block;
    }
</style>