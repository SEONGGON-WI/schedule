<template>
  <div>
    <v-alert
      class="mx-1 my-0"
      dense
      text
      :type="type"
      v-if="dialog"
    >
      {{ text }}
    </v-alert>
  </div>
</template>
<script>

export default {
  name: "schedulebaner",
  components: {
  },
  props: [
    'type', 'text'
  ],
  data: () => ({
    dialog: false,
  }),
  created() {
    this.dialog = true;
    setTimeout(function() {
      this.$emit('close');
      this.dialog = false;
    }.bind(this), 3000);
  },
  computed: {
  },
  methods: {
  },
}
</script>