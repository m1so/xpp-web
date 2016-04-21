<template>
    <div class="box box-primary {{ data.length <= 5 ? 'box-half-height' : '' }}">
        <div class="box-header with-border">
            <h3 class="box-title">
                {{ title }}
            </h3>
            <i class="fa fa-info-circle pull-right"></i>
        </div>
        <div class="box-body">
            <table class="table table-condensed">
                <tbody>
                    <tr v-for="field in data">
                        <td class="td-key">
                            <input v-model="field.key" type="text" class="form-control" placeholder="Param #{{ $index + 1 }}">
                        </td>
                        <td class="td-value">
                            <input v-model="field.value" type="text" class="form-control" placeholder="Value #{{ $index + 1 }}">
                        </td>
                        <td class="td-action">
                            <i @click="removeFromData(field)" class="fa fa-trash-o fa-left-pad-7"></i>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="row">
                <div class="col-xs-12 col-top-pad-15">
                    <button @click="addNewRow" type="button" class="btn btn-default btn-block btn-sm">
                        Add new parameters
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            title: {
                type: String,
                required: true
            },
            data: {
                type: Array,
                require: true
            }
        },

        methods: {
            /**
             * Remove element with give index from the data property of this component
             *
             * @param field
             */
            removeFromData(field) {
                this.data.$remove(field);
            },

            /**
             * Append new row (empty object) to data of this component
             */
            addNewRow() {
                this.data.push({});
            }
        }
    }
</script>

<style>

</style>
