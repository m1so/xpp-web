<template>
    <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">
                        <i v-if="loading" class="fa fa-spin fa-spinner"></i>
                        My documents
                        <span v-if="message" class="label label-default bg-purple">{{ message }}</span>
                    </h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div v-for="folder in folders">
                        <h4><i class="fa fa-folder-o"></i> {{ $key ? $key : 'Uncategorized' }}</h4>
                        <ul class="todo-list">
                            <li v-for="document in folder">
                                <a href="/documents/{{ document.id }}" class="text-black">{{ document.title }}</a>
                                <span v-if="document.public" class="label bg-purple">Public</span>
                                <button @click="remove(document)" type="button" class="btn btn-xs btn-danger btn-doc-list pull-right">Delete</button>
                                <button @click="edit(document)" type="button" class="btn btn-xs bg-yellow btn-doc-list pull-right">Edit</button>
                                <a href="/documents/{{ document.id }}" type="button" class="btn btn-xs btn-primary btn-doc-list pull-right">Open in editor</a>
                            </li>
                        </ul>
                        <hr>
                    </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer clearfix no-border">
                    <create-document-modal></create-document-modal>
                </div>
            </div>

            <modal title="Edit document" :show.sync="showEdit" effect="zoom">
                <div slot="modal-body" class="modal-body">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" v-model="editedDocument.title" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Folder name</label>
                        <input type="text" v-model="editedDocument.folder" class="form-control">
                    </div>

                    <div class="checkbox">
                        <label>
                            <input v-model="editedDocument.public" type="checkbox"> Public
                        </label>
                    </div>
                </div>
                <div slot="modal-footer" class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="showEdit = false">Cancel</button>

                    <button type="button" class="btn btn-success" @click="save()">Save</button>
                </div>
            </modal>
    </div>
</template>

<script type="text/babel">
    import groupBy from 'lodash/collection/groupBy';

    import CreateDocumentModal from './DocumentCreateModal.vue';
    import { modal } from 'vue-strap';

    const emptyDocument = {
        title: null
    };

    export default {
        props: {
            documents: {
                required: true
            }
        },

        data() {
            return {
                showEdit: false,
                loading: false,
                message: null,
                editedDocument: emptyDocument
            };
        },

        computed: {
            folders() {
                return groupBy(this.documents, doc => doc.folder);
            }
        },

        methods: {
            edit(document) {
                this.editedDocument = document;
                this.showEdit = true;
            },

            remove(document) {
                this.message = `Deleting document "${document.title}"`;
                this.loading = true;

                this.$http.delete(
                    `/documents/${document.id}`
                ).then(response => {
                    this.message = response.data.message;
                    this.loading = false;

                    setTimeout(() => {
                        this.message = null;
                    }, 3000);

                    this.documents.$remove(document);
                }).catch(response => {
                    this.loading = false;

                    console.log(response); // eslint-disable-line
                });
            },

            save() {
                this.message = `Saving document: "${this.editedDocument.title}"`;
                this.loading = true;

                this.$http.put(
                    `/documents/${this.editedDocument.id}`,
                    this.editedDocument
                ).then(response => {
                    this.documents.$set(
                        this.documents.findIndex((item) => item.id === this.editedDocument.id),
                        response.data.data
                    );

                    this.message = response.data.message;
                    this.loading = false;
                    this.editedDocument = emptyDocument;
                    this.showEdit = false;

                    setTimeout(() => {
                        this.message = null;
                    }, 3000);
                }).catch(response => {
                    this.loading = false;
                    this.editedDocument = emptyDocument;
                    this.showEdit = false;

                    console.log(response); // eslint-disable-line
                });
            }
        },

        components: {
            'create-document-modal': CreateDocumentModal,
            modal
        }
    };
</script>

<style>
    .btn-doc-list {
        margin-right: 3px;
        margin-left: 3px;
    }
</style>
