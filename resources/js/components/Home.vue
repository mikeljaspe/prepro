<template>
    <v-card class="elevation-12">
        <v-toolbar
        color="primary"
        dark
        flat
        >
            <v-toolbar-title>Upload CSV</v-toolbar-title>
        </v-toolbar>
        <v-card-text>
            <v-file-input 
                v-model="parseCSV" 
                accept=".csv" 
                label="CSV Upload"
                class="mb-2"
            ></v-file-input>
            <v-btn 
                color="primary" 
                @click="readCSV()"
                block
                large  
            >
                Submit
            </v-btn>
            <v-divider></v-divider>
            <h4 class="mt-3">Output are in the console.</h4>
        </v-card-text>
    </v-card>
</template>
<script>
  export default {
    data: () => ({
        parseCSV: [],
    }),
    methods: {
        readCSV () {
            Papa.parse(this.parseCSV, {
                skipEmptyLines: true,
                complete: function(results) {
                    axios.post(process.env.MIX_APP_URL + '/api/computecsv', [results.data]).then((response) => {
                        console.log(response.data)
                    }).catch(error => {
                        console.log(error)
                    })
                }
            })
        }
    }
}
</script>