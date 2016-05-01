<template>
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">
                {{ title }}
            </h3>
            <div class="box-tools pull-right">
                <div class="form-inline">
                    <button v-if="type === 'option'" @click="modal = true" class="btn btn-link">
                        Add interactively
                    </button>
                    <input type="text" v-model="search" class="form-control inputbox-search" placeholder="Search...">
                </div>
            </div>
        </div>
        <div class="box-body">
            <p v-if="data.length && ! searchResults.length">No results found for query "{{ search }}". <a @click="search = null">Clear search?</a></p>
            <table class="table table-condensed">
                <tbody>
                    <tr v-for="token in searchResults">
                        <td class="editor-input">
                            <input v-model="token.value[0]" :readonly="type === 'option' ? 'true' : null"
                                   type="text" class="form-control" placeholder="Key #{{ $index + 1 }}">
                        </td>
                        <td class="editor-input">
                            <input v-model="token.value[1]" type="text" class="form-control" placeholder="Value #{{ $index + 1 }}">
                        </td>
                        <td class="editor-input-action">
                            <button @click="remove(token)" class="btn btn-block btn-default"><i class="fa fa-minus text-danger"></i></button>
                        </td>
                    </tr>

                    <tr>
                        <td class="editor-input-key">
                            <input v-model="newField.key" class="form-control editor-input-new" type="text" placeholder="Add key">
                        </td>
                        <td class="editor-input-value">
                            <input v-model="newField.value" class="form-control editor-input-new" type="text" placeholder="Add value">
                        </td>
                        <td class="editor-input-action">
                            <button @click="add" class="btn btn-block btn-default"><i class="fa fa-plus text-success"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <nav v-if="totalPages > 1" class="text-center">
                <ul class="pagination pagination-sm pagination-inputbox">
                    <li :class="[currentPage === 0 ? 'disabled' : '']">
                        <a @click="setPage(currentPage - 1)" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <li v-for="pageNumber in totalPages" :class="[pageNumber === currentPage ? 'active' : '']">
                        <a @click="setPage(pageNumber)">{{ pageNumber + 1 }}</a>
                    </li>
                    <li :class="[currentPage === Math.max(0, totalPages - 1) ? 'disabled' : '']">
                        <a @click="setPage(currentPage + 1)" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <modal title="Add options" :show.sync="modal" effect="zoom" width="400">
            <div slot="modal-header" class="modal-header">
                <h3 class="modal-title">
                    Add or replace options
                </h3>
                <div class="pull-rigth">
                    <input v-model="optionSearch" class="form-control" type="text" placeholder="Search for an option">
                </div>
            </div>
            <div slot="modal-body" class="modal-body">
                <div v-for="option in optionsMap | filterBy optionSearch in 'display' 'description'">
                    <h4>{{ option.display }}</h4>

                    <strong>Usage</strong>: {{{ option.usage }}}

                    <p>{{{ option.description }}}</p>

                    <div class="input-group">
                        <input type="text" class="form-control form-inline" v-model="optionValue[$index]">
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-primary" @click="addOption(option.display, optionValue[$index])">
                                Add/Replace {{ option.display }}
                            </button>
                        </span>
                    </div>
                    <hr>
                </div>
            </div>
            <div slot="modal-footer" class="modal-footer">
                <button type="button" class="btn btn-default" @click='modal = false'>Exit</button>
            </div>
        </modal>
    </div>
</template>

<script>
import { modal } from 'vue-strap';
import { OPTIONS_MAP, OPTION } from '../../xpp/constants';

export default {
    props: ['type', 'title', 'data', 'itemsPerPage', 'parser'],

    created() {
        this.optionsMap = OPTIONS_MAP;
    },

    data() {
        return {
            search: null,
            currentPage: 0,
            // itemsPerPage: 6,
            resultCount: 0,
            newField: {
                key: null,
                value: null
            },
            optionValue: Object.keys(OPTIONS_MAP).map(option => ''),
            optionSearch: null,
            modal: false
        };
    },

    computed: {
        searchResults() {
            return this.$eval("data | filterBy search in 'value[0]' | paginate");
        },
        totalPages() {
            return Math.ceil(this.resultCount / this.itemsPerPage);
        }
    },

    methods: {
        add() {
            // Don't add anything if neither input is filled out
            if (this.newField.key === null || this.newField.value === null) {
                return;
            }

            // Add with newline option to make nicer ode output
            this.parser.add(this.newField.key, this.newField.value, this.type, true);

            // Reset the values
            this.newField.key = null;
            this.newField.value = null;
        },

        addOption(key, value) {
            this.parser.replace(key, value, OPTION);
            this.$dispatch('generate-input');
        },

        remove(token) {
            this.parser.remove(token);
        },

        setPage(pageNumber) {
            this.currentPage = pageNumber;
        }
    },

    filters: {
        paginate(list) {
            this.resultCount = list.length;

            if (this.currentPage >= this.totalPages) {
                this.currentPage = Math.max(0, this.totalPages - 1);
            }

            let index = this.currentPage * this.itemsPerPage;
            return list.slice(index, index + this.itemsPerPage);
        }
    },

    components: {
        modal
    }
}
</script>

<style>
    .editor-input-action {
        width: 20px;
    }

    .editor-input-new {
        border-color: darken(#ccc, 15%);
        box-shadow: none;
    }

    .inputbox-search {
        width: 80px;
        border-style: none;
    }

    .pagination-inputbox {
        margin-top: 5px;
        margin-bottom: 0px;
    }
</style>
