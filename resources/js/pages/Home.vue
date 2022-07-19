<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <form @submit.prevent="getData()">
              <div class="row">
                <div class="col-md-5">
                  <input
                    class="form-control"
                    type="date"
                    v-model="start_date"
                    required
                  />
                </div>
                <div class="col-md-5">
                  <input
                    class="form-control"
                    type="date"
                    :min="start_date"
                    :max="getMaxAllowedDate"
                    v-model="end_date"
                    required
                  />
                </div>
                <div class="col-md-2">
                  <button type="submit" class="btn btn-primary">Search</button>
                </div>
              </div>
            </form>
          </div>

          <div class="card-body">
            <div class="row">
              <div class="col-md-12">
                <p><b>Fastest Asteroid</b>: {{ getFastedAsteroid }}</p>
                <p><b>Closest Asteroid</b>: {{ getClosestAsteroid }}</p>
                <p><b>Average Size</b>: {{ getAvgSizeAsteroid }}</p>
              </div>
              <div class="col-md-12">
                <div class="hr"></div>
              </div>
              <div class="col-md-12" v-if="chartStatus">
                <bar-chart
                  :labels="chart.labels"
                  :datasets="chart.datasets"
                ></bar-chart>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import BarChart from "../components/BarChart.vue";
export default {
  components: { BarChart },
  data() {
    return {
      start_date: "",
      end_date: "",
      astroid_data: null,
      chartStatus: false,
      chart: {
        labels: [],
        datasets: [],
      },
    };
  },
  computed: {
    getMaxAllowedDate() {
      if (this.start_date !== "") {
        var date = new Date(this.start_date);
        date.setDate(date.getDate() + 7);
        var dd = date.getDate();
        var mm = date.getMonth() + 1; //January is 0!
        var yyyy = date.getFullYear();
        if (dd < 10) {
          dd = "0" + dd;
        }

        if (mm < 10) {
          mm = "0" + mm;
        }
        return yyyy + "-" + mm + "-" + dd;
      }
      return "";
    },
    getFastedAsteroid() {
      var all = [];
      if (
        this.astroid_data?.near_earth_objects &&
        Object.keys(this.astroid_data?.near_earth_objects).length > 0
      ) {
        for (const key of Object.keys(this.astroid_data?.near_earth_objects)) {
          all.push(
            _.maxBy(this.astroid_data.near_earth_objects[key], function (o) {
              return o
                .close_approach_data[0].relative_velocity.kilometers_per_hour;
            })
          );
        }
        var max = _.maxBy(all, function (o) {
          return o.close_approach_data[0].relative_velocity.kilometers_per_hour;
        });
        return `${max.id} - (${max.close_approach_data[0].relative_velocity.kilometers_per_hour} km/h)`;
      }
      return "";
    },
    getClosestAsteroid() {
      var all = [];
      if (
        this.astroid_data?.near_earth_objects &&
        Object.keys(this.astroid_data?.near_earth_objects).length > 0
      ) {
        for (const key of Object.keys(this.astroid_data?.near_earth_objects)) {
          all = all.concat(this.astroid_data.near_earth_objects[key]);
        }
        var min = _.minBy(all, function (o) {
          return o.close_approach_data[0].miss_distance.kilometers;
        });
        return `${min.id} - (${min.close_approach_data[0].miss_distance.kilometers} km)`;
      }
      return "";
    },
    getAvgSizeAsteroid() {
      var min = [];
      var max = [];
      if (
        this.astroid_data?.near_earth_objects &&
        Object.keys(this.astroid_data?.near_earth_objects).length > 0
      ) {
        for (const key of Object.keys(this.astroid_data?.near_earth_objects)) {
          min = min.concat(this.astroid_data.near_earth_objects[key].map(v => v.estimated_diameter.kilometers.estimated_diameter_min));
          max = max.concat(this.astroid_data.near_earth_objects[key].map(v => v.estimated_diameter.kilometers.estimated_diameter_max));
        }
        var minAvg = min.reduce( ( p, c ) => p + c, 0 ) / min.length;
        var maxAvg = max.reduce( ( p, c ) => p + c, 0 ) / max.length;
        return `Min Avg.: ${minAvg} km, Max Avg: ${maxAvg} km`;
      }
      return "";
    },
  },
  methods: {
    getData() {
      this.chartStatus = false;
      this.astroid_data = null;
      axios
        .post("/api/neo", {
          start_date: this.start_date,
          end_date: this.end_date,
        })
        .then((response) => {
          this.astroid_data = response.data;
          this.makeChartData(response.data);
        });
    },

    makeChartData(data) {
      if (
        data?.near_earth_objects &&
        Object.keys(data?.near_earth_objects).length > 0
      ) {
        var labels = [];
        var dataset = {
          data: [],
          label: "Number of Asteroids",
          backgroundColor: "#f87979",
        };
        for (const key of Object.keys(data?.near_earth_objects)) {
          labels.push(key);
          dataset.data.push(data.near_earth_objects[key].length);
        }
        this.chart.labels = labels;
        this.chart.datasets = [dataset];
        this.chartStatus = true;
      }
    },
  },
};
</script>