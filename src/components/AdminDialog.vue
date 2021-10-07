<template>
  <v-dialog v-click-outside="close" v-model="dialog" max-width="70%" min-height="50%">
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
      <v-btn class="mx-2 mb-5 confirm_action" color="error" plain @click="close" outlined>
        いいえ
      </v-btn>
      <v-btn class="mx-2 mb-5 confirm_action" color="info" plain @click="accept" outlined>
        はい
      </v-btn>
      <v-spacer></v-spacer>
    </v-sheet>
  </v-dialog>
</template>
<script>

export default {
  name: "admindialog",
  components: {
  },
  props: [
    'text'
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
    accept() {
      this.dialog = false;
      this.$emit("accept");
    },
  },
}
</script>