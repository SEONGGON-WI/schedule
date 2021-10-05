<template>
  <v-app id="inspire">
    <v-app-bar app color="primary" dark>
      ADMIN
    </v-app-bar>
    <v-main>
      <div>
        default page
      </div>
      <v-btn @click="post">
        axios php
        <v-icon>add</v-icon>
      </v-btn>
    </v-main>
  </v-app>
</template>

<script>
import axios from 'axios'
export default {
  name: 'AdminLogin',
  data: () => ({
    event: [
      {'name': "wi", 'date': '2021-10-05', 'comment': 'testData'},
      {'name': "gon", 'date': '2021-10-07', 'comment': 'testForm'},
    ]
  }),
  methods: {
    async post() {
      console.log("post");
      const url = "/vue/app/test.php";
      const data = this.event;
      await axios.post(url, data).then(function(response) {
        console.log(response);
      }.bind(this))
    },
  },
}
</script>
