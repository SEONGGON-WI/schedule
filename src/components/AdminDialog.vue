<template>
  <v-dialog v-click-outside="cancel" v-model="dialog" max-width="70%" min-height="50%">
    <v-sheet
      class="text-center d-inline-block"
      color="blue-grey darken-3"
      dark
      min-width="100%" 
      max-height="100%"
    >
      <div color="white" class="text--lighten-1 ma-2 confirm">
        {{ text }}
      </div>

      <v-spacer></v-spacer>
      <v-btn class="mx-2 mb-5 confirm_action" color="error" plain @click="cancel" outlined>
        <v-icon>cancel</v-icon>キャンセル
      </v-btn>

      <v-btn class="mx-2 mb-5 confirm_action" color="info" plain @click="save_data" outlined v-if="action == 1">
        <v-icon>save</v-icon>はい
      </v-btn>
      
      <v-btn class="mx-2 mb-5 confirm_action" color="success" plain @click="restore_data" outlined v-if="action == 2">
        <v-icon>settings_backup_restore</v-icon>はい
      </v-btn>
      <v-spacer></v-spacer>
    </v-sheet>
  </v-dialog>
</template>
<script>

export default {
  name: "checkdialog",
  components: {
  },
  props: [
    'text', 'action'
  ],
  data: () => ({
    dialog: false,
  }),
  created() {
    this.dialog = true;
  },
  computed: {
  },
  methods: {
    cancel() {
      this.dialog = false;
      this.$emit("cancel");
    },
    save_data() {
      this.dialog = false;
      this.$emit("save_data");
    },
    restore_data() {
      this.dialog = false;
      this.$emit("restore_data");
    },
  },
}
</script>