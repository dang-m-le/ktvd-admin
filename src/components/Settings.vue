<script setup>
</script>

<script>
export default {
    props: ['appmenu'],
    expose: ['unloadMenu'],
    data() {
        return {
            settings: {
                current_school_year: 2022,
                registration_year: 2023,
                first_student_fee: 100,
                next_student_fee: 80,
                senior_student_fee: 20,
                senior_student_age: 16,
                min_age: 5,
                cutoff_date: 'Sep-1'        
            }
        }
    },

    mounted() {
        this.loadMenu();
        this.loadSettings();
    },

    beforeRouteLeave(to, from) {
        this.unloadMenu();
        return true;
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

        async loadSettings() {
            try {
                var resp = await this.$root.post("settings.php", {op: 'get'});
                for (var attr in resp.settings) {
                    this.settings[attr] = resp.settings[attr];
                }
            }
            catch(ex) {
                this.$root.notify(resp.message, 'error');
            }
        },

        async saveSettings() {
            try {
                var resp = await this.$root.post('settings.php', {op: 'save', settings: this.settings});
                console.log(resp);
                this.$root.notify(resp.message, 'success');
            }
            catch (ex) {
                this.$root.notify(ex.message, 'error');
            }
        }
    }
}
</script>

<template>
    <template>
        <v-container ref="cmenu">
            <v-btn variant="text" @click="saveSettings" color="blue" prepend-icon="mdi-check-circle">Save</v-btn>
        </v-container>
    </template>
    <v-card class="settings" flat width="400">
        <v-card-text class="px-2 py-0">
        <v-text-field hide-details v-model.trim="settings.current_school_year"
                        label="Current School Year"></v-text-field>
        <v-text-field hide-details v-model.trim="settings.registration_year"
                        label="Registration Year"></v-text-field>
        <v-text-field hide-details v-model.trim="settings.first_student_fee"
                        prefix="$" label="First Student Fee"></v-text-field>
        <v-text-field hide-details  v-model.trim="settings.next_student_fee"
                        prefix="$" label="Next Student Fee"></v-text-field>
        <v-text-field hide-details v-model.trim="settings.senior_student_fee"
                        prefix="$" label="Senior Student Fee"></v-text-field>
        <v-text-field hide-details v-model.trim="settings.senior_student_age"
                        label="Senior Student Age"></v-text-field>
        <v-text-field hide-details v-model.trim="settings.min_age"
                        label="Minimum Age"></v-text-field>
        <v-text-field hide-details v-model.trim="settings.cutoff_date"
                        label="Cutoff Date (Mon-day)"></v-text-field>
        </v-card-text>
    </v-card>
</template>