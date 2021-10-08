<template>
  <v-dialog v-click-outside="close" v-model="dialog" @keydown.enter="upload" @keydown.esc="close" max-width="70%" min-height="50%">
    <v-sheet
      class="text-center d-inline-block"
      color="blue-grey darken-3"
      dark
      min-width="100%" 
      max-height="100%"
    >
      <div color="white" class="text--lighten-1 ma-2 confirm">
        登録しますか？
      </div>
      <v-spacer></v-spacer>
      <v-btn class="mx-2 mb-5 confirm_action" height="60" color="error" plain @click="close" outlined>
        いいえ
      </v-btn>
      <v-btn class="mx-2 mb-5 confirm_action" height="60" color="info" plain @click="upload" outlined>
        はい
      </v-btn>
      <v-spacer></v-spacer>
    </v-sheet>
  </v-dialog>
</template>
<script>

export default {
  name: "staffdialog",
  components: {
  },
  props: [
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
    close() {
      this.dialog = false;
      this.$emit("close");
    },
    upload() {
      this.dialog = false;
      this.$emit("upload");
    },
  },
}
</script>