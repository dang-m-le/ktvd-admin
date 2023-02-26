<script setup>
import { bracket } from "@/utils.js";
</script>

<script>
export default {
    expose: ['open'],
    data() {
        return {
            showing: false,
            username: '',
            old_password_required: true,
            old_password: '',
            new_password: '',
            confirm_password: '',

            message: '',
            resolve: null
        };
    },

    computed: {
        right_as_rain() {
            return (this.new_password)
                && (this.confirm_password == this.new_password)
                && (this.old_password || !this.old_password_required)
            ;
        }
    },

    methods: {
        close() {
            this.showing = false;
        },

        open(name, require_old_password) {
            this.username = name;
            this.message = "Change password account "+bracket(this.username)+".";
            this.old_password_required = require_old_password;
            this.old_password = '';
            this.new_password = '';
            this.confirm_password = '';
            return new Promise(res => {
                this.resolve = res;
                this.showing = true;
            });
        },

        cancel() {
            this.showing = false;
            this.resolve(false);
        },

        async resetPassword() {
            try {
                var resp = await this.$root.post('users.php', {
                    op: 'password',
                    username: this.username,
                    old_password: this.old_password,
                    new_password: this.new_password
                });
                console.log(resp);
                this.showing = false;
                this.$root.notify(resp.message, 'success');
                this.resolve(true);
            }
            catch(ex) {
                this.message = ex.message;
            }    
        }
    }
}
</script>


<template>
    <v-dialog v-model="showing" max-width="300">
        <v-card class="settings">
            <v-card-title><span class="headline">Reset Password</span></v-card-title>
            <v-card-subtitle>{{ message }}</v-card-subtitle>
            <v-card-text>
                <v-text-field v-if="old_password_required" persistent-hint dense outlined label="Old Password" type="password"
                    v-model.trim="old_password"></v-text-field>
                <v-text-field persistent-hint dense outlined label="New Password" type="password"
                    v-model.trim="new_password"></v-text-field>
                <v-text-field persistent-hint dense outlined label="Confirm Password" type="password"
                    v-model.trim="confirm_password"></v-text-field>
            </v-card-text>
            <v-card-actions>
                <v-btn text color="green darken-1" @click="resetPassword"
                    :disabled="!right_as_rain"
                    ><v-icon>mdi-check-circle</v-icon>Reset</v-btn>
                <v-spacer></v-spacer>
                <v-btn text color="red darken-1" @click="cancel"
                    ><v-icon>mdi-cancel</v-icon>Cancel</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>