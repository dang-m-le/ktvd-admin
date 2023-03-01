<script setup>
import { ref, reactive, onMounted, inject } from 'vue'
import { alike, bracket, FilterSet, vn2us } from '@/utils.js'

const notify = inject('notify');
const confirm = inject('confirm');
const post = inject('post');
const loadMenu = inject('loadMenu');

var props = defineProps()

var _cmenu = ref(null);
onMounted(() => {
    loadMenu(_cmenu.value.$el);
    getStudentListing();
});

function somebody(opt) {
    return Object.assign({
        id:0, 
        fid:0, 
        saintname: '',
        lastname: '', 
        middlename: '', 
        firstname: '', 
        nickname: '', 
        birthdate: '2001-01-01', 
        baptized: false, 
        confession: false, 
        confirmed: false, 
        communion: false
    }, opt || {});
};

//
// Listing
//
const _filter = ref("");
const listing = reactive([]);

async function getStudentListing() {
    try {
        var resp = await post('student.php', {op: 'list'});
        resp.students.forEach(s => {
            s.cname = vn2us(catname(s));
            listing.push(s);
        });
    }
    catch (ex) {
        notify(ex.message, 'error');
    }
}

function filteredListing() {
    var v = [];
    var filter = new FilterSet(_filter.value || '.', ['cname']);
    listing.forEach(student => {
        if (filter.match(student)) {
            v.push(student);
        }
    });
    return v;
}

//
// editing
//
var edit = reactive(somebody());
var orig = reactive(somebody());

async function newStudent() {
    showStudent(somebody());
}

async function showStudent(student)
{
    if (alike(edit, orig)) {
        Object.assign(orig, Object.assign(edit, student));
    }
    else {
        var ans = await confirm("Save Student", "Save student "+bracket(edit.id)+" "+edit.firstname+"?");
        if (ans == 'no' || (ans == 'yes' && await saveStudent())) {
            Object.assign(orig, Object.assign(edit, student));
        }
    }
}

function selectStudent(student) {
    showStudent(student);
}

async function saveStudent() {
    return true;
}

async function deleteStudent() {
    var ans = await confirm("Delete Student", "Delete student "+backet(orig.id)+" "+orig.firstname+"?");
    if (ans == 'yes') {
        console.log(ans);
    }
}

function catname(student) {
    var s = student.lastname;
    if (student.middlename) {
        s += " "+student.middlename;
    }
    s += " "+student.firstname;
    if (student.nickname && student.nickname != student.firstname) {
        s += " ("+student.nickname+")";
    }
    return s;
}

</script>

<template>
    <template>
        <v-container ref="_cmenu" class="users-menu">
            <v-btn variant="text" @click="newStudent" color="green"
                ><v-icon size="x-large">mdi-account-plus</v-icon><span class="d-none d-sm-flex">New</span></v-btn>
            <v-btn variant="text" @click="saveStudent" color="blue"
                :disabled="!edit.firstname"
                ><v-icon size="x-large">mdi-account-check</v-icon><span class="d-none d-sm-flex">Save</span></v-btn>
            <v-btn variant="text" @click="deleteStudent" color="orange"
                :disabled="!orig.id"
                ><v-icon size="x-large">mdi-account-remove</v-icon><span class="d-none d-sm-flex">Delete</span></v-btn>
        </v-container>
    </template>

    <v-container style="display:flex; flex-flow: wrap; align-contents:flex-start;">
        <v-card flat width="400">
            <v-card-text density="compact">
                <v-row class="pa-2">
                    <v-col style="text-align:left;">ID: <span class="ident">{{ edit.id }}</span></v-col>
                    <v-col style="text-align:left;">Family ID: <span class="ident">{{ edit.fid }}</span></v-col>
                </v-row>
                <v-text-field hide-details label="Saint Name" v-model.trim="edit.saintname"></v-text-field>
                <v-text-field hide-details label="Last Name" v-model.trim="edit.lastname"></v-text-field>
                <v-text-field hide-details label="Middle Name" v-model.trim="edit.middlename"></v-text-field>
                <v-text-field hide-details label="First Name" v-model.trim="edit.firstname"></v-text-field>
                <v-text-field hide-details label="Nick Name" v-model.trim="edit.nickname"></v-text-field>
                <v-text-field hide-details label="Birthdate" v-model.trim="edit.birthdate"></v-text-field>
                <v-row class="pt-2">
                    <v-col class="px-2 py-0"><v-checkbox density="compact" hide-details label="Baptized" color="green"
                        v-model="edit.baptized"></v-checkbox></v-col>
                    <v-col class="px-2 py-0"><v-checkbox density="compact" hide-details label="Confession" color="purple"
                        v-model="edit.confession"></v-checkbox></v-col>
                </v-row>
                <v-row>
                    <v-col class="px-2 py-0"><v-checkbox density="compact"  hide-details label="Confirmed" color="red"
                        v-model="edit.confirmed"></v-checkbox></v-col>
                    <v-col class="px-2 py-0"><v-checkbox density="compact"  hide-details label="Communion" color="blue"
                        v-model="edit.communion"></v-checkbox></v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <v-card class="listing pa-0" rounded="0" width="400">
            <v-card-title  class="px-2 py-0">
                <v-text-field class="filter" v-model="_filter" hide-details
                    prepend-icon="mdi-magnify" clearable
                ></v-text-field>
            </v-card-title>
            <v-card-text class="px-2 py-0">
                <v-list height="400">
                    <v-list-item class="student" v-for="student in filteredListing()" :key="student.id"
                        @click="()=>selectStudent(student)"
                        :title="catname(student)"
                    ><v-list-item-subtitle>
                        <span>{{ student.birthdate }}</span>
                    </v-list-item-subtitle>
                    </v-list-item>
                </v-list>
            </v-card-text>
        </v-card>

    </v-container>
</template>

<style>

.ident {
    font-weight: 700;
    color: #25a;
}

.listing .student.v-list-item {
    text-align: left;
}

.listing .student.v-list-item .v-list-item-title {
    color: #25a;
}

</style>