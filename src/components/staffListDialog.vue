<template>
  <v-dialog content-class="custom_dialog" v-model="dialog" persistent>
    <v-container class="pa-0" fluid>
      <v-card color="grey lighten-4">
        <v-toolbar color="primary" dark>
          <v-toolbar-title class="mx-2">
            スタッフ管理
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn class="error mx-2 botton_size" @click="close">
            <v-icon>cancel</v-icon>キャンセル
          </v-btn>
        </v-toolbar>
          <v-data-table 
            item-class="mt-2"
            name="staff-list-table"
            :headers="headers" 
            :items="items" 
            item-key="name"
            :search="search"
            :footer-props="footer"
            :options.sync="options"
            no-data-text="データがありません"
          > 
            <template v-slot:top>
              <v-text-field
                v-model="search"
                label="キーワードで検索"
                class="mx-4 pt-4"
                clearable
              ></v-text-field>
            </template>
            <template v-slot:item.action="{ item }">
              <v-btn class="info mx-5 my-3" icon color="white" @click="edit(item)" :disabled="!valid">
                <v-icon>edit</v-icon>
              </v-btn>
              <v-btn class="error mx-5 my-3" icon color="white" @click="remove_check(item)">
                <v-icon>delete</v-icon>
              </v-btn>
            </template>
          </v-data-table>
      </v-card>
    </v-container>
    <v-dialog v-model="edit_dialog" persistent width="70%">
      <v-container class="pa-0" fluid>
        <v-card color="grey lighten-4">
          <v-toolbar color="primary" dark>
            <v-toolbar-title class="mx-2">
              クライアント編集
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <v-btn class="success mx-2 botton_size" @click="edit_staff">
              <v-icon>edit</v-icon>保存
            </v-btn>
            <v-btn class="error mx-2 botton_size" @click="edit_close">
              <v-icon>cancel</v-icon>キャンセル
            </v-btn>
          </v-toolbar>
          <v-card-text>
            <v-form ref="form" v-model="valid">
            <v-row>
              <v-col cols="6">
                <v-text-field
                  v-model="staff_name"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'" 
                  :rules="[rules.required]"
                  @keydown.enter="enter(1)"
                  label="名前"
                  single-line
                ></v-text-field>
              </v-col>
              <v-col cols="6">
                <v-text-field
                  ref="password"
                  v-model="staff_password"
                  :lang="$vuetify.breakpoint.mobile ? 'en' : 'ja'"
                  @keydown.enter="enter(2)"
                  label="パスワード"
                  single-line
                ></v-text-field>
              </v-col>
            </v-row>
            </v-form>
          </v-card-text>
        </v-card>
      </v-container>
    </v-dialog>
    <alert
      @close="alert_show = false"
      :text="alert_text"
      v-if="alert_show"
    ></alert>

    <admin-dialog
      @accept="remove"
      @close="remove_dialog = false"
      v-if="remove_dialog"
      text="削除しますか？"
    ></admin-dialog>
  </v-dialog>
</template>
<style lang="scss">
[name=staff-list-table] tbody tr:nth-of-type(odd){
  background: rgba(170, 200, 99,0.2); 
}
</style>
<script>
import AdminDialog from '@/components/AdminDialog.vue';
import alert from './alert.vue';
import axios from 'axios';
export default {
  name: "stafflistdialog",
  components: {
    AdminDialog,
    alert,
  },
  props: [
  ],
  data: () => ({
    items: [],
    headers: [
      { value:"name", text:"名前", width: "40%", align: 'start'},
      { value:"access_time", text:"更新日", width: "30%", align: 'start'},
      { value:"action", text:"編集", width:"30%", align: 'center', sortable: false}
    ],
    footer : {
      itemsPerPageText:"1ページあたりの行数",
      itemsPerPageOptions:[5,10,25,-1]
    },
    options : {
      page:1,
      itemsPerPage: 10,
    },
    search: '',
    alert_text: '',
    alert_show: false,
    dialog: false,
    edit_dialog: false,
    remove_dialog: false,
    remove_item: {name: ''},
    current_staff_name: '',
    staff_name: '',
    staff_password: '',
    root_folder: '',
    valid: true,
    rules:{
      required: value => !!value || '省略不可',
    },
  }),
  created() {
    this.root_folder = this.$store.getters.root_folder
    this.fetch_data()
    this.dialog = true;
  },
  computed: {
  },
  methods: {
    fetch_data() {
      const url = this.root_folder + "/app/adminGetStaff.php";
      const data = {}
      axios.post(url, data).then(function(response) {
        if (response.data.status == true && response.data.data != '') {
          this.items = response.data.data
        } else {
          this.items = []
        }
      }.bind(this))
    },
    edit(item) {
      this.current_staff_name = item.name
      this.staff_name = item.name
      this.edit_dialog = true;
    },
    edit_staff() {
      if (this.staff_password == '') {
        this.edit_dialog = false
        return
      }
      const url = this.root_folder + "/app/adminEditStaff.php";
      const data = {
        current_name: this.current_staff_name,
        name: this.staff_name.replace(/^\s+|\s+$/gm,''),
        password: this.staff_password.replace(/^\s+|\s+$/gm,''),
      }
      axios.post(url, data).then(function(response) {
        if (response.data.status == true) {
          this.fetch_data()
        } else {
          this.alert(response.data.message)
        }
        this.staff_password = ''
      }.bind(this))
      this.edit_dialog = false
    },
    edit_close() {
      this.staff_password = ''
      this.edit_dialog = false
    },
    remove_check(item) {
      this.remove_item = item
      this.remove_dialog = true
    },  
    remove() {
      const url = this.root_folder + "/app/adminRemoveStaff.php";
      const data = {
        name: this.remove_item.name,
      }
      axios.post(url, data).then(function(response) {
        if (response.data.status == true) {
          this.fetch_data()
        } else {
          this.alert(response.data.message)
        }
        this.remove_item = {name: ''}
      }.bind(this))
      this.remove_dialog = false
    },
    enter(index) {
      switch (index) {
        case 1:
          this.$refs.password.focus()
          break;

        case 2:
          if(this.staff_name == '' || this.current_staff_name == '') {
            break
          } else {
            this.edit_staff()
            break
          }

        default:
          break;
      }
    },
    alert(text) {
      this.alert_text = text;
      this.alert_show = true;
    },
    close() {
      this.dialog = false;
      this.$emit("close");
    },
  },
}
</script>