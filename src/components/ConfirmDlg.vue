<script setup>
</script>

<script>
export default {
    props: {
        width: { type: Number, default: 400 }
    },
    expose: ['open'],
    data() {
        return {
            mode: 'YNC',
            headline: '',
            prompt: '',
            resolve: null,
            reject: null,

            showing: false
        };

    },

    methods: {
        close() {
            this.showing = false
        },

        open(mode, headline, prompt) {
            return new Promise(res => {
                this.resolve = res;
                this.mode = mode;
                this.headline = headline;
                this.prompt = prompt;
                this.showing = true;
            });
        },

        confirmed(ans) {
            this.showing = false;
            this.resolve(ans);
        }
    }
}
</script>

<template>
    <v-dialog v-model="showing" persistent :max-width="width">
        <v-card class="confirmation">
        <v-card-title>
            <span class="headline">{{headline}}</span>
        </v-card-title>
        <v-card-text class="prompt" v-if="prompt">{{ prompt }}</v-card-text>
        <v-card-actions>
            <v-btn color="green darken-1" text @click="confirmed('yes')"
                ><v-icon>mdi-check-circle</v-icon>{{
                {X:'Close', Y:'Yes', E:'Continue', O:'Okay', '':'Okay'}[ mode.charAt(0) ]
            }}</v-btn>
            <v-spacer></v-spacer>
            <v-btn color="red darken-1" text @click="confirmed('no')"
                    v-if="mode.substring(1,2)=='N'"><v-icon>mdi-close-circle</v-icon>No</v-btn>
            <v-btn color="blue darken-1" text @click="confirmed('cancel')"
                    v-if="mode.slice(-1)=='C'"><v-icon>mdi-cancel</v-icon>Cancel</v-btn>
        </v-card-actions>
        </v-card>
    </v-dialog>
</template>