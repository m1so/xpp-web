<template>
    <button type="button" class="btn btn-default" @click="showModal = true"><i class="fa fa-plus"></i> Create a new document</button>

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
    import { modal } from 'vue-strap';

    export default {
        data() {
            return {
                title: null,
                folder: '',
                showModal: false,
                loading: false
            };
        },

        methods: {
            createDocument() {
                this.loading = true;

                let formData = new FormData();
                formData.append('title', this.title);
                formData.append('folder', this.folder);

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
                    console.log('Error creating document', response.status); // eslint-disable-line no-console
                });
            }
        },

        components: {
            modal
        }
    };
</script>
