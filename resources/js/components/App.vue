<template>
<v-app>
    <v-content>
      <v-container
        class="fill-height"
        fluid
      >
        <v-row
          align="center"
          justify="center"
        >
          <v-col
            cols="12"
            sm="8"
            md="4"
          >
          <router-view></router-view>
          </v-col>
        </v-row>
      </v-container>
    </v-content>
    </v-app>
</template>

<script>
  export default {
    data: () => ({
      drawer: null,
    }),
  }
</script>