<template>
    <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title">
                        <i v-if="loading" class="fa fa-spin fa-spinner"></i>
                        List of public documents
                        <span v-if="message" class="label label-default bg-purple">{{ message }}</span>
                    </h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div v-for="folder in folders">
                        <h4><i class="fa fa-folder-o"></i> {{ $key ? $key : 'Uncategorized' }}</h4>
                        <ul class="todo-list">
                            <li v-for="document in folder">
                                <a href="/documents/{{ document.id }}" class="text-black">
                                    {{ document.title }}
                                    <span class="label label-primary">by {{ document.user.name }}</span>
                                </a>
                                <button @click="duplicate(document)"
                                        type="button"
                                        class="btn btn-xs btn-primary btn-doc-list pull-right"
                                >
                                    Duplicate
                                </button>
                            </li>
                        </ul>
                        <hr>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
    </div>
</template>

<script type="text/babel">
    import groupBy from 'lodash/collection/groupBy';

    export default {
        props: {
            documents: {
                required: true
            }
        },

        data() {
            return {
                loading: false,
                message: null
            };
        },

        computed: {
            folders() {
                return groupBy(this.documents, doc => doc.folder);
            }
        },

        methods: {
            duplicate(document) {
                this.message = `Duplicating document "${document.title}"`;
                this.loading = true;

                this.$http.post(
                    `/documents/duplicate/${document.id}`,
                    {}
                ).then(response => {
                    this.message = response.data.message;
                    this.loading = false;

                    setTimeout(() => {
                        this.message = null;
                    }, 3000);
                }).catch(response => {
                    this.loading = false;

                    console.log(response); // eslint-disable-line
                });
            }
        }
    };
</script>

<style>
    .btn-doc-list {
        margin-right: 3px;
        margin-left: 3px;
    }
</style>
