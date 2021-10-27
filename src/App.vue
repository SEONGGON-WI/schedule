<template>
  <div id="app">
    <!-- <div id="nav"> -->
      <!-- <router-link to="/">Home</router-link> |
      <router-link to="/about">About</router-link> -->
    <!-- </div> -->
    <router-view></router-view>
  </div>
</template>
<script>
export default {
  name: 'App',
  components: {
    
  },
  created() {
    this.disableScroll
    this.removeEvent
  },
  data: () => ({
  }),
  methods: {
    disableScroll() {
      document.querySelector('body').addEventListener('touchmove', this.removeEvent, { passive: false });
      document.querySelector('body').addEventListener('onclick', this.removeEvent, { passive: false });
      document.querySelector('body').addEventListener('mousewheel', this.removeEvent, { passive: false });
    },
    removeEvent(e) {
      e.preventDefault();
      e.stopPropagation();
    }
  }
}
</script>
