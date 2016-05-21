<template>
    <button type="button" class="btn btn-default" @click="openModal()">
        <i class="fa fa-plus"></i> Create a new document
    </button>

    <modal :show.sync="showModal" effect="fade" width="400">
        <div slot="modal-header" class="modal-header">
            <h4 class="modal-title">
                Create a new document
            </h4>
        </div>

        <div slot="modal-body" class="modal-body">
            <div class="form-group">
                <label>Title</label>
                <input type="text" class="form-control" placeholder="Untitled" v-model="title">
            </div>

            <div class="form-group">
                <label>Optional: Folder name</label>
                <input type="text" class="form-control" placeholder="Uncategorized" v-model="folder">
            </div>

            <div class="form-group">
                <label>Optional: ODE file</label>
                <input type="file" v-el:file>
            </div>

            <div class="checkbox">
                <label>
                    <input v-model="public" type="checkbox"> Optional: Public
                </label>
            </div>

            <div v-if="errorMsg" class="callout callout-danger">
                <h4>Validation failed!</h4>
                <ul>
                    <li v-for="msg in errorMsg">{{ msg }}</li>
                </ul>
            </div>
        </div>

        <div slot="modal-footer" class="modal-footer">
            <button type="button" class="btn btn-default" @click="showModal = false">Close</button>
            <button type="button" class="btn btn-success" @click="createDocument()" :disabled="loading ? 'disabled' : null">
                <i class="fa fa-spinner fa-spin" v-show="loading"></i> {{ loading ? 'Loading' : 'Create' }}
            </button>
        </div>
    </modal>
</template>

<script>
    import values from 'lodash/object/values';

    import { modal } from 'vue-strap';

    export default {
        data() {
            return {
                title: null,
                folder: '',
                public: false,
                showModal: false,
                errorMsg: null,
                loading: false
            };
        },

        methods: {
            openModal() {
                this.showModal = true;
                this.errorMsg = null;
            },

            createDocument() {
                this.loading = true;
                this.errorMsg = null;

                let formData = new FormData();
                formData.append('title', this.title);
                formData.append('folder', this.folder);
                formData.append('public', this.public ? 1 : 0);

                if (this.$els.file.files.length === 1) {
                    formData.append('ode', this.$els.file.files[0]);
                }

                this.$http.post(
                    '/documents',
                    formData
                ).then(function (response) {
                    this.showModal = false;
                    this.loading = false;

                    window.location.assign(
                        window.location.protocol + '//' + window.location.host + '/documents/' + response.data.data.id
                    );
                }).catch(function (response) {
                    this.loading = false;
                    this.errorMsg = values(response.data);
                    console.log('Error creating document', JSON.stringify(response.data)); // eslint-disable-line no-console
                });
            }
        },

        components: {
            modal
        }
    };
</script>
