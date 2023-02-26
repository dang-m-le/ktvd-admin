<script setup>
import { postURL } from '@/utils.js';
import ktvdLogo from '@/assets/KTVD-icon-small.png';
</script>

<script>
export default {
    expose: ['open'],
    data() {
        return {
            username: "",
            password: "",
            persistent: false,
            authenticating: false,
            message: "",

            showing: false,
            resolve: null,
            reject: null,
        }
    },

    methods: {
        open(message) {
            return new Promise((res) => {
                this.message = message || '-';
                this.resolve = res;
                this.showing = true;
            })
        },

        cancel() {
            this.showing = false;
            if (this.resolve) {
                this.resolve(null);
            }
        },

        async authenticate() {
            this.authenticating = true;
            this.message = '';
            try {
                var resp = await postURL("login.php", {
                    username: this.username,
                    password: this.password,
                    persistent: this.persistent
                });
                this.showing = false;
                if (this.resolve) {
                    this.resolve(resp);
                }
            }
            catch (ex) {
                console.log(ex);
                this.message = ex.message;
            }
            finally {
                this.authenticating = false;
            }
        },
    }
}
</script>

<template>
    <v-dialog v-model="showing" persistent max-width="320px">
        <v-card class="login-dlg">
        <v-card-title>
            <img width="40" :src="ktvdLogo">
            <span class="headline ml-4">Sign In</span>
            <v-spacer></v-spacer>
        </v-card-title>
        <v-card-subtitle>{{ message }}</v-card-subtitle>
        <v-card-text>
            <v-text-field dense v-model="username"
                label="Username:" outlined autocomplete persistent-placeholder required
                ></v-text-field>
            <v-text-field dense v-model="password" type="password"
                label="Password:" outlined autocomplete persistent-placeholder required
                @keyup.enter="authenticate"
                ></v-text-field>
            <v-checkbox dense v-model="persistent" label="Remember me"
                ></v-checkbox>
        </v-card-text>
        <v-card-actions>
            <v-btn text color="green darken-1" :disabled="authenticating"
                @click="authenticate"
                ><v-icon>mdi-check-circle</v-icon>Okay</v-btn>
            <v-spacer></v-spacer>
            <v-btn text color="red darken-1" :disabled="authenticating"
                @click="cancel"
                ><v-icon>mdi-cancel</v-icon>Cancel</v-btn>
        </v-card-actions>
        </v-card>
    </v-dialog>
</template>