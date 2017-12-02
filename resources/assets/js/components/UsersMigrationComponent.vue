<template>
    <div class="row">
        <div class="col-md-12">

            <adminlte-vue-box color="success" id="migrate-users-options-box">
                <span slot="title">Migration options</span>
                <!-- Minimal style -->

                <p>By default only pending users to migrate are executed!</p>

                <!-- radio -->
                <div class="form-group">
                    <div class="checkbox icheck">
                        <label>
                            <input style="display:none;" type="checkbox" name="remember"
                                   ref="all" v-model="form.all"> All users
                        </label>
                    </div>
                    <div class="checkbox icheck">
                        <label>
                            <input style="display:none;" type="checkbox" name="remember"
                                   ref="overwrite" v-model="form.overwrite">
                            Overwrite (be careful! current data on destination could be lost).
                            User id on destination will be preserved!
                        </label>
                    </div>
                </div>

            </adminlte-vue-box>

            <adminlte-vue-box color="primary" id="migrate-users-box" :collapsable="false" :removable="false">
                <span slot="title">Migrate users</span>
                <a class="btn btn-app" @click="migrate" :class="{ disabled: migrating }">
                    <i class="fa fa-play"></i> Migrate
                </a>
                <a class="btn btn-app" @click="stopMigration" :class="{ disabled: ! migrating }">
                    <i class="fa fa-stop"></i> Stop
                </a>
                <adminlte-vue-progress :value="progress"></adminlte-vue-progress>
                <i v-if="migrating" id="user-migration-progress-spinner" class="fa fa-refresh fa-spin"></i><span> {{progressMessage}} </span>
            </adminlte-vue-box>

            <!--TODO create adminlte-vue-box variant for chat boxes -->
            <div class="box box-primary direct-chat direct-chat-primary" id="users-migration-history">
                <div class="box-header with-border">
                    <h3 class="box-title">Migrations history</h3>

                    <div class="box-tools pull-right">
                        <span data-toggle="tooltip" title="3 New migrations" class="badge bg-light-blue">3</span>
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="direct-chat-messages">
                        <div class="direct-chat-msg" v-for="migration in migrations">
                            <div class="direct-chat-info clearfix">
                                <span class="direct-chat-name pull-left">User {{ migration.user ? migration.user.name : 'removed' }}|{{  migration.user ? migration.user.email : 'removed' }} ( {{ migration.user_id }} ) migrated from <span :title="migration.source_user">JSON</span></span>
                                <span class="direct-chat-timestamp pull-right">{{ migration.created_at }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overlay" v-if="fetchingMigrationHistory">
                    <i class="fa fa-refresh fa-spin"></i>
                </div>
            </div>

            <!--TODO create adminlte-vue-box variant for chat boxes -->
            <div class="box box-primary direct-chat direct-chat-primary" id="batch-users-migration-history">
                <div class="box-header with-border">
                    <h3 class="box-title">Batch Migrations history</h3>

                    <div class="box-tools pull-right">
                        <span data-toggle="tooltip" title="3 New migrations" class="badge bg-light-blue">3</span>
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="direct-chat-messages">
                        <div class="direct-chat-msg" v-for="batch in batches">
                            <div class="direct-chat-info clearfix">
                                <span class="direct-chat-name pull-left">Batch => State: {{ batch.state }} | Migrated: {{ batch.accomplished }} | Errors: {{ batch.incidences }} | Last update: {{ batch.updated_at }} </span>
                                <span class="direct-chat-timestamp pull-right">{{ batch.created_at }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overlay" v-if="fetchingBatchMigrationHistory">
                    <i class="fa fa-refresh fa-spin"></i>
                </div>
            </div>

        </div>
    </div>
</template>

<script>

  import store from '../Store'
  import Form from 'acacha-forms'
  import axios from 'axios'

  export default {
    name:'UsersMigration',
    data() {
      let form = new Form({ all: '', overwrite: '' })
      return {
        form: form,
        progress: 0,
        progressMessage: '',
        migrating: false,
        fetchingMigrationHistory: false,
        fetchingBatchMigrationHistory: false,
        batchId: null,
        migrations: [],
        batches: []
      }
    },
    props: {
      uri: {
        type: String,
        default: '-migration/totalNumberOfUsers'
      }
    },
    methods: {
      migrate() {
        this.migrating = true
        this.progressMessage = 'Starting migration!'
        this.progress= 0;
        this.fetchingMigrationHistory = true
        let apiUrl = store.apiUri + '-migration/migrate'
        axios.post(apiUrl)
          .then( response => {
            this.batchId = response.data.id
          })
          .catch( error => {
            console.log(error);
          });
      },
      stopMigration() {
        if (this.migrating) {
          this.progressMessage = 'Stopping migration!'
          let apiUrl = store.apiUri + '-migration/migrate-stop'
          axios.post(apiUrl,{ batch_id: this.batchId })
            .then( response => {
              this.migrating = false
              this.batchId = null
              this.progressMessage = 'Migration stopped!'
            })
            .catch( error => {
              console.log(error);
            });
        }
      },
      subscribeToProgressBarEvents() {
        this.$echo.channel('progress-bar').listen('ProgressBarStatusHasBeenUpdated', (payload) => {
          if(payload.id === 'users-migration-progress-bar') {
            this.progressMessage = payload.message
            this.progress = payload.progress
            if (this.progress == 100) {
              this.migrating = false
              this.fetchingMigrationHistory = false
              this.fetchMigrationHistory()
              this.fetchBatchesMigrationHistory()
            }
          }
        });
        this.$echo.channel('users-migration').listen('UserHasBeenMigrated', (payload) => {
          this.$events.fire('update-user-migration-dashboard')
        });
      },
      subscribeToMigrationHistoryEvents() {
        this.$echo.channel('users-migration').listen('UserMigrationHasBeenPersisted', (payload) => {
          if (!this.migrating) this.fetchMigrationHistory()
        })
      },
      fetchMigrationHistory() {
        this.fetchingMigrationHistory = true
        let migrationHistoryRequest = new Form( {}, true )
        migrationHistoryRequest.get(store.apiUri + '-migration/history').then( response => {
          this.migrations = response.data.data
          this.fetchingMigrationHistory = false
        })
      },
      fetchBatchesMigrationHistory() {
        this.fetchingBatchMigrationHistory = true
        let migrationHistoryRequest = new Form( {}, true )
        migrationHistoryRequest.get(store.apiUri + '-migration/batch_history')
          .then( response => {
            this.batches = response.data
            this.fetchingBatchMigrationHistory = false
          })
      },
      initializeIcheck() {
        $(this.$refs.all).iCheck({
          checkboxClass: 'icheckbox_square-blue',
          radioClass: 'iradio_square-blue',
          increaseArea: '20%'
        }).on('ifChecked', event => {
          this.form.set('all', true)
          this.form.errors.clear('all')
        }).on('ifUnchecked', event =>  {
          this.form.set('all', '')
        })
        $(this.$refs.overwrite).iCheck({
          checkboxClass: 'icheckbox_square-blue',
          radioClass: 'iradio_square-blue',
          increaseArea: '20%'
        }).on('ifChecked', event =>  {
          this.form.set('overwrite', true)
          this.form.errors.clear('overwrite')
        }).on('ifUnchecked', event =>  {
          this.form.set('overwrite', '')
        })

      }
    },
    watch: {
      'form.all': function (value) {
        if (value) {
          $(this.$refs.all).iCheck('check')
        } else {
          $(this.$refs.all).iCheck('uncheck')
        }
      },
      'form.overwrite': function (value) {
        if (value) {
          $(this.$refs.overwrite).iCheck('check')
        } else {
          $(this.$refs.overwrite).iCheck('uncheck')
        }
      }
    },
    mounted() {
      this.subscribeToProgressBarEvents()
      this.fetchMigrationHistory()
      this.fetchBatchesMigrationHistory()
      this.subscribeToMigrationHistoryEvents()
      this.initializeIcheck()
    }
  }
</script>