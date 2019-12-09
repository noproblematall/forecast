<template>
  <div  @click="sidebar_away">
    <div class="all_bar">
      <!-- menubar -->
      <nav class="navbar nav_green">
        <!-- Brand -->
        <div class="navbar-brand">
          <a class="navbar-brand" href="https://vcmov.epsa.cl/" target="_blank"><i class="fa fa-long-arrow-left"></i></a>
          <span>{{current_name}}</span>
        </div>
        <!-- Toggler/collapsibe Button -->
        <button class="navbar-toggler" type="button" @click.stop="sidebar">
          <span class="navbar-toggler-icon"></span>
        </button>
      </nav>
      <!-- nav-bar -->
      <div class="bottom_nav">
        <div class="row">
          <div class="col-4">
            <div class="float-left date" @click="get_for_date_left(position-1)"><i class="fa fa-caret-left"></i>&nbsp;<span>{{date_left | date_format}}</span></div>
          </div>
          <!-- Get real time data -->
          <div class="col-4">
            <div class="text-center">
              <div class="clock" @click="get_data_for_realtime()">
                <span>Tpo. Real</span>
              </div>
            </div>
          </div>
          <div class="col-4">
            <div class="float-right date" @click="get_for_date_right(position)"><span>{{date_right | date_format}}</span>&nbsp;<i class="fa fa-caret-right"></i></div>
          </div>
        </div>
        <div class="clearfix"></div>
      </div>
      <!-- Right Side Bar -->
      <div class="sidebar" @click.stop="" :class="{sidebar_active: sidebar_flag}">
        <div class="top_text">
          <div class="float-left">Menu</div>
          <div class="float-right">
            <i class="fa fa-close fa-lg" @click="sidebar"></i>
          </div>
        </div>
        <ul id="menu">
          <div class="page">
            <li @click="get_for_page('wave')">
              <span>Oleaje</span>
            </li>
            <li @click="get_for_page('wind')">
              <span>Viento</span>
            </li>
          </div>
          <hr />
          <div class="area" v-if="loaded">
            <li v-for="item in location" :key="item.locationId"><span @click="get_for_location(item.locationId)">{{item.name}}</span></li>
          </div>
        </ul>
      </div>

    </div>

    <div class="card clock_card centered-element">
      <div class="card-body text-center" v-if="clock_flag && page_flag">
        <div class="row">
          <div class="col-12">
            <p>{{ text_for_page }}, actualizada&nbsp;&nbsp;{{time_for_realtime | only_time_format}}</p>
          </div>
        </div>
        <div class="row">
          <div class="col-4">
            <p>Dirección</p>
            <div class="detail_data">
              <div class="row">
                <div class="col-4">
                  <i class="fa fa-arrow-circle-up arrow" :style="get_rotate(dir_for_realtime)"></i>
                </div>
                <div class="col-8">
                  <div style="width: 50px;">{{dir_for_realtime}}º</div>
                  <div style="margin-top: -5px;">{{get_mark(dir_for_realtime)}}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-4">
            <p>Altura(H<sub>1/3</sub>)</p>
            <div class="detail_data">
              <div class="row">
                <div class="col-12" style="padding:0;margin-top:-5px;">
                  <span style="font-size:35px;">{{toFixed_value(hm0_for_realtime)}}</span>
                  <span style="vertical-align: top;">m</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-4">
            <p>Periodo</p>
            <div class="detail_data">
              <div class="row">
                <div class="col-12" style="padding:0;margin-top:-5px;">
                  <span style="font-size:35px;">{{toFixed_value(tp_for_realtime)}}</span>
                  <span style="vertical-align: top;">s</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body text-center" v-if="clock_flag && !page_flag">
        <div class="row">
          <div class="col-12">
            <p>{{ text_for_page }}, actualizada&nbsp;&nbsp;{{time_for_realtime | only_time_format}}</p>
          </div>
        </div>
        <div class="row">
          <div class="col-4">
            <p>Dirección</p>
            <div class="detail_data" style="color: #96b200 !important;">
              <div class="row">
                <div class="col-4">
                  <i class="fa fa-arrow-circle-up arrow" style="color: #96b200 !important;" :style="get_rotate(winddir_for_realtime)"></i>
                </div>
                <div class="col-8">
                  <div style="width: 50px;">{{winddir_for_realtime}}º</div>
                  <div style="margin-top: -5px;">{{get_mark(winddir_for_realtime)}}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-4">
            <p>Velocidad</p>
            <div class="detail_data" style="color: #96b200;">
              <div class="row">
                <div class="col-12" style="padding:0;margin-top:-5px;">
                  <span style="font-size:35px;">{{toFixed_value(windmag_for_realtime)}}</span>
                  <span style="vertical-align: top;">kn</span>
                </div>
              </div>
            </div>
          </div>
          <div class="col-4">
            <p>Racha</p>
            <div class="detail_data" style="color: #96b200;">
              <div class="row">
                <div class="col-12" style="padding:0;margin-top:-5px;">
                  <span style="font-size:35px;">{{toFixed_value(wind3s_for_realtime)}}</span>
                  <span style="vertical-align: top;">kn</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-body text-center" style="height: 100px;" v-if="loading_flag">
        <div class="spinner-grow text-success" style="margin-top:25px;"></div>

      </div>
    </div>

    <transition name="slide" mode="out-in">
        <router-view :init_location="init_location" :key="$route.fullPath"></router-view>
    </transition>

    <footer class="text-center footer" >
        <h5>Empresa Portuaria San Antonio</h5>
        <p>Av. Barros Luco Nº1613, of. 8A</p>
        <p>San Antonio - Chile.</p>
        <p>Tel. (56) 35 2586000 / Fax (56) 35 2586010.</p>
        <br>
        <p><b><a href="#">© EPSA</a></b></p>
    </footer>
  </div>
</template>

<script>
  import moment from 'moment';
  import { eventBus } from '../app';
  export default {

    data() {
      return {
        loaded: false,
        sidebar_flag: true, // show the sidebar status
        clock_flag: false, // show the real-time window status
        loading_flag: false, // show the real time data loading status
        date: [], // save the all date from database
        date_temp: [], // temporary array variable for date[]
        location: [], // save the location from database
        base_url: '', // define the base URL of site
        uri: '',
        date_left: '', // once user click left date icon, show data correspond to the date range
        date_right: '',// once user click right date icon, show data correspond to the date range
        position: 1,
        init_location: '', //define the first location 
        current_location: '',// define the current location
        dir_for_realtime: '',// define the direction value for realtime data
        hm0_for_realtime: '',// define the Altura value for realtime data
        tp_for_realtime: '',// define the Tp value for realtime data
        time_for_realtime: '',// define the time value for realtime data
        range: [11.25,33.75,56.25,78.75,101.25,123.75,146.25,168.75,191.25,213.75,236.25,258.75,281.25,303.75,326.25,348.75],
        mark: ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'],
        wind3s_for_realtime: '', // define the wind 3s value for realtime data
        winddir_for_realtime: '',// define the wind direction value for realtime data
        windmag_for_realtime: '',// define the wind mag value for realtime data
        page_flag: true,
        current_name: '',
        all_data_flag: false,
        text_for_page: 'Oleaje',// define the current page name
      };
    },
    async mounted () {// once page load, first of all called(vue default function)
      this.base_url = document.getElementById('base_url').value;
      this.uri = this.base_url + 'api/date_location';
      this.loaded = false;
      this.init();
    },
    methods: {
      sidebar: function() { // show the sidebar status
        this.sidebar_flag = !this.sidebar_flag;
      },
      sidebar_away: function() {
        this.sidebar_flag = true;
      },
      init () {//get the all data (arrow date, location, current location ) from database
        try {
            this.axios.get(this.uri).then(res => {
              if(Object.keys(res.data).length > 0){
                this.date = res.data.dates;
                this.location = res.data.location;
                this.loaded = true;
                this.init_location = res.data.location[0].locationId;
                this.current_name = res.data.location[0].name;
                this.current_location = this.init_location;
                this.date_left = this.date[0];
                this.date_right = this.date[1];
              }
            }).catch(function (error) {
                if(error != ''){
                }
            })
            .finally(function () {
            });
        } catch (e) {
        console.error(e)
        }
      },
      moment: function(){// return date process object
        return moment();
      },
      get_for_location: function(value){// get the location data from 
        this.sidebar_flag = true;
        if(this.all_data_flag){
            this.date = []
            this.date = this.date_temp
            this.date_temp = []
            this.all_data_flag=false
        }
        this.date_left = this.date[0];
        this.date_right = this.date[1];
        this.position = 1;
        this.current_location = value;
        for (let i = 0; i < this.location.length; i++) {
          if(this.location[i].locationId == value){
            this.current_name = this.location[i].name;
          }
        }

        if(this.$route.name == 'wave' || this.$route.name == 'init_wave'){
          this.$router.push({ name: 'wave', params: { location: value ,date: 'start'}}).catch(err => {
          });
        }else if(this.$route.name == 'wind' || this.$route.name == 'init_wind'){
            this.$router.push({ name: 'wind', params: { location: value ,date: 'start'}}).catch(err => {
          });
        }
      },
      get_for_page(value){// once menu clicked, get the page to menu values
        this.current_location = 1
        this.current_name = this.location[this.current_location - 1].name
      
        this.sidebar_flag = true;
        this.clock_flag = false;
        if(this.all_data_flag){
            this.date = []
            this.date = this.date_temp
            this.date_temp = []
            this.all_data_flag=false
        }
        this.date_left = this.date[0];
        this.date_right = this.date[1];
        this.position = 1;
        if(value == 'wave' || value == 'init_wave'){
          this.$router.push({ name: 'init_wave'}).catch(err => {
            
          });
        }else if(value == 'wind' || value == 'init_wind'){
          this.$router.push({ name: 'init_wind'}).catch(err => {
            
          });
        }
      },
      get_for_date_left: function (value) { //once user click left date arrow , get the all data for chart
        if(value != 0){
          this.date_left = this.date[value-1];
          this.date_right = this.date[value];
          this.position = value;
          if(this.$route.name == 'wave' || this.$route.name == 'init_wave'){
          this.$router.push({ name: 'wave', params: { location: this.current_location ,date: 'left_'+value}}).catch(err => {
            
          });
          }else if(this.$route.name == 'wind' || this.$route.name == 'init_wind'){
            this.$router.push({ name: 'wind', params: { location: this.current_location ,date: 'left_'+value}}).catch(err => {
              
            });
          }
        }
      },
      get_for_date_right: function (value) { //once user click right date arrow , get the all data for chart
        if(value != (this.date.length - 1)){
          this.date_left = this.date[value];
          this.date_right = this.date[value+1];
          this.position = value+1;
          if(this.$route.name == 'wave' || this.$route.name == 'init_wave'){
          this.$router.push({ name: 'wave', params: { location: this.current_location ,date: 'right_'+value}}).catch(err => {
            
          });
          }else if(this.$route.name == 'wind' || this.$route.name == 'init_wind'){
            this.$router.push({ name: 'wind', params: { location: this.current_location ,date: 'right_'+value}}).catch(err => {
              
            });
          }
        }
      },
      get_data_for_realtime: function () { // Once user click realtime title, get the realtime date
        if(this.clock_flag){
          this.clock_flag=false;
          return;
        }
        this.loading_flag=!this.loading_flag;
        let page = this.$route.name;
        let uri = ''
        if(page == 'wave' || page == 'init_wave'){
          this.text_for_page = 'Oleaje'
          this.page_flag = true;
          if(this.loading_flag){
            this.axios.post(this.base_url + 'api/get_for_realtime_wave', {
              'end_point' : 'dir_tp',
            }).then(res => {
              this.dir_for_realtime = res.data.value;
              this.time_for_realtime = moment.utc(res.data.date).local().format();
            });
            this.axios.post(this.base_url + 'api/get_for_realtime_wave', {
              'end_point' : 'h_3',
            }).then(res => {
              this.hm0_for_realtime = res.data.value;
            });
            this.axios.post(this.base_url + 'api/get_for_realtime_wave', {
              'end_point' : 'tp',
            }).then(res => {
              this.tp_for_realtime = res.data.value;
              this.loading_flag=false;
              this.clock_flag=!this.clock_flag;
            });
          }
        }else if(page == 'wind' || page == 'init_wind'){
          this.text_for_page = 'Viento'
          this.page_flag = false;
          if(this.loading_flag){
            this.axios.post(this.base_url + 'api/get_for_realtime_wind', {
              'end_point' : 'avg_wind_direction',
            }).then(res => {
              this.time_for_realtime = moment.utc(res.data.date).local().format();
              this.winddir_for_realtime = res.data.value;
            });
            this.axios.post(this.base_url + 'api/get_for_realtime_wind', {
              'end_point' : 'avg_wind_speed',
            }).then(res => {
              this.windmag_for_realtime = res.data.value * 1.94384;
            });
            this.axios.post(this.base_url + 'api/get_for_realtime_wind', {
              'end_point' : 'wind_speed_3s_gust',
            }).then(res => {
              this.wind3s_for_realtime = res.data.value * 1.94384;
              this.loading_flag=false;
              this.clock_flag=!this.clock_flag;
            });
          }
        }

      },
      get_rotate: function(value){ // rotate the arrow icon according to direction range
        let rotate = value - 180;
        return 'transform: rotate('+ rotate +'deg);';
      },
      get_mark: function(value){ // change the letter(N,W...) correspond to direction values(0~360)
        for (let i = 0; i < this.range.length; i++) {
          if(value < this.range[i]){
            return this.mark[i];
          }
        }
        return '-';
      },
      toFixed_value: function(value){// change the values to float type
        return parseFloat(value).toFixed(1);
      },
    },
    created() { // vue.js default function
        let vm = this
        eventBus.$on('initPage', () => {// show the first page
            vm.all_data_flag = false
            vm.date = []
            vm.date = vm.date_temp
            vm.date_temp = []
            vm.date_left = vm.date[0]
            vm.date_right = vm.date[1]
            vm.get_for_location(vm.current_location)
        });
        eventBus.$on('viewAllData', () => { // show chart for first page
            vm.all_data_flag = true
            vm.date_temp = vm.date
            vm.date = []
            vm.date = [vm.date_temp[0], vm.date_temp[vm.date_temp.length-1]]
            vm.date_left = vm.date[0]
            vm.date_right = vm.date[1]
        });
    },
    filters: {
      date_format: function(value){// get the filtered date from database.
        if (!value) return '';
        let date = [];
        date = value.split(' ')[0];
        date = date.split('-');
        return date[2] + '/' + date[1];
      },
      only_time_format: function (value) {// get the only time value from database.
        if (!value) return '';
        let time = [];
        time = value.split('T')[1];
        time = time.split(':');
        return time[0] +':'+ time[1];
      }
    }

  };
</script>

<style>
body {
  font-family: Rubik;
  background-color: #ffffff;
}
.display_none {
  display: none;
}
section {
  margin-top: 50px;
}
.slide-enter {
  opacity: 0;
}
.slide-enter-active {
  animation: slide-in 0.5s ease-out forwards;
  transition: opacity 0.5s;
}

.slide-leave-active {
  animation: slide-out 0.5s ease-out forwards;
  transition: opacity 0.5s;
  opacity: 0;
}
@keyframes slide-in {
  from {
    transform: translateY(100px);
  }
  to {
    transform: translateY(0);
  }
}
@keyframes slide-out {
  from {
    transform: translateY(0);
  }
  to {
    transform: translateY(20px);
  }
}
.animated {
  -webkit-animation-duration: 400ms;
  -moz-animation-duration: 400ms;
  animation-duration: 400ms;
}
.all_bar {
  position: fixed;
  top: 0;
  width: 100%;
  z-index: 11;
}
.nav_green {
  background: #0e8cb3;
  color: white;
  padding: 2px 10px;
}
.nav_green .navbar-toggler {
  color: rgba(255, 255, 255, 0.5);
  border-color: rgba(255, 255, 255, 0.3);
}
.nav_green .navbar-brand {
  color: white;
}
.nav_green .navbar-toggler-icon {
  background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3e%3cpath stroke='rgba(255, 255, 255, 1)' stroke-width='3' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

.sidebar {
  position: fixed;
  top: 0;
  right: 0;
  width: 20em;
  height: 100%;
  padding: 0px;
  z-index: 12;
  background-color: #44484c;
  overflow-x: hidden;
  transition: all 0.5s;
}
.sidebar_active {
  right: -20em;
}
nav {
  height: 45px;
}
.bottom_nav {
  height: 32px;
  padding: 4px 1px;
  background: #0a6d8c;
  color: white;
}
.bottom_nav .text-center i {
  font-size: 19px;
  margin-top: 2px;
}
.bottom_nav .date {
  cursor: pointer;
}
.bottom_nav .date i, .navbar-brand i {
  color:#f9ad1f;font-size:15px;
}
.navbar-brand {
  padding: 0;
}
.bottom_nav .clock {
  width: 80px;
  border-radius: 3px;
  cursor: pointer;
  margin: auto;
  background: #0e8cb3;
}
.top_text {
  height: 45px;
  background: #1e96bb;
  font-size: 18px;
  color: #fff;
  padding-left: 10px;
  padding-top: 10px;
  padding-right: 10px;
}
.bottom_nav div.row {
  margin: 0;
}
#menu {
  padding-top: 20px;
}

a {
  text-decoration: none;
}
#menu li a,
#menu li {
  color: white;
  padding-bottom: 10px;
}
.page li span {
  cursor: pointer;
}
.page li span:hover {
  color: #a7a6a6;
}
#menu .area li span {
  cursor: pointer;
}
#menu .area li span:hover {
  color: #a7a6a6;
}
#menu .active a {
  color: #a7a6a6;
  cursor: default;
  text-decoration: none;
}
i {
  cursor: pointer;
}

li {
  list-style-type: none;
}
hr {
  border-top: 1px solid rgba(251, 247, 247, 0.2);
}
.clock_card {
  width: 500px;
  margin: 3px auto;
  box-shadow: 0 0 3px rgba(0, 0, 0, 0.5);
  border: none;
}
.clock_card p {
  margin-bottom: 5px;
}
.card-body {
  padding: 10px;
}
.detail_data {
  width: 80px;
  margin: auto;
  color: rgb(73, 144, 226);
}
.detail_data i {
  font-size: 40px;
  color: rgb(73, 144, 226);
  cursor:default;
}
.detail_data_wind {
  width: 80px;
  margin: auto;
  color: #96b200;
}
.detail_data_wind i {
  font-size: 40px;
  color: #96b200;
  cursor:default;
}
.tbl_wave_top thead th{
  padding:0;
}
.tbl_wave_top td{
  padding:5px;
}
table {
  text-align: center;
  width: 600px;
  margin: auto;
  margin-top: 30px;
}
.table thead th, .table th, .table td {
  border: none;
}
.footer {
  margin-top: 50px;
  padding: 20px;
  color: white;
  background-size: cover;
  background-position: center;
}
.footer p {
  margin-bottom: 5px;
}

.tbl_windir{
  margin-top: -25px;
  margin-bottom: 0px;
}
.tbl_timeinterval{
  margin-top: 4px;
  margin-bottom: 0px;
}

.sp_firstmargin{
  margin-right:-115%;
}
.tbl_windir td{
  border: 1px solid #b3b3b3;
  padding: 0px;
}
.tbl_timeinterval td {
    padding: 0px;
}
.td_first{
  width: 4.5%;
  border-left:none  !important;
  border-top:none !important;
  border-bottom:none !important;
}
.td_main{
  width:8%;
}
.td_last{
  width: 0.7%;
  border-right:none  !important;
  border-top:none !important;
  border-bottom:none !important;
}
.label_main{
  width: 9.5%;
}
.tbl_windir i {
    font-size: 30px;
    color: #96b200;
    cursor: default;
}
.tbl_detail i{
    color: #96b200;
    cursor: default;
}
.btn_excel{
  background-color: #1fbbeb;
  color: white;
  outline: 0;
  box-shadow: 0;
  border: 0px;
}
.btn_excel:hover{
  background-color: #6dcbe7;
  color: white;
}

.centered-element {
  z-index: 10;
  position: fixed;
  left: 50%;
  margin-left: -250px;
  top: 79px;
}
.custom_container {
  margin-top: 85px;
}

.altura{
  position: absolute;
  top: -35px;
  left: 26px;
}
.Periodo{
  position: absolute;
  top: -35px;
  right: 130px;
}
.Direction{
  position: absolute;
  top: -35px;
  right: 20px;
}

.scale img {
    border-radius: 50%;
    width: 50px;
    position: absolute;
    top: -40px;
    left: 100px;
    cursor: pointer;
}
.scale img:hover {
    opacity: .8;
}
.y_responsive table {
    left: 55px;
}
.wave_unit table {
    top: -20px;
    left: 67px;
}
.sec_wave_two{
    margin-top:20px;
}
.sec_wind_two{
    margin-top:-20px;
}
.chart_above_div{
  text-align:center;
}
@media only screen and (max-width: 600px) {
  .chart_above_div{
    left: 12px;
  }

  .y_responsive button {
    top: -93px !important;
    left: calc(50% - 0px) !important;
  }
  
  .wave_unit button {
   
    top: -93px !important;
    left: calc(50% - 52px) !important;
 
  }
  .sec_wave_two{
    margin-top:120px !important;
  }
  .sec_wind_two{
    margin-top:80px !important;
  }
  .scale img {
    top: -100px;
    left: 25px;
  }
  .wind_third_section{
    margin-top:0px !important;
  }
  .clock_card, table {
    width: 100%;
  }
  .detail_data span:first-child{
    font-size: 30px !important;
  }
  .detail_data_wind span:first-child{
    font-size: 30px !important;
  }

  .centered-element {
    left: 0 !important;
    margin-left: 0 !important;
  }

  .table th, .table td {

    padding-left: 0.05rem;
    padding-right: 0.05rem;

  }
  .td_first{
    width: 5% !important;
  }
  .td_main{
    width: 4% !important;
  }
  .td_last{
    width: 0.9% !important;
  }
  .label_first{
    width: 5% !important;
  }
  .label_main{
    width: 4% !important;
  }
  .label_last{
    width: 2% !important;
  }
  .tbl_windir td{
    padding: 0px !important;
  }
  .tbl_windir i {
    font-size: 20px;
  }
   .tbl_timeinterval {
    margin-top: -3px !important;
    width: 90%;
    margin-left: 12%;
  }
  .sp_margin, .sp_firstmargin{
    font-size: 12px;
  }
}
@media only screen and (max-width: 500px) {
  .td_first{
    width: 6% !important;
  }
}
@media only screen and (max-width: 450px) {
  .td_first{
    width: 6.7% !important;
  }
  .td_main{
    width: 3.7% !important;
  }
  .td_last{
    width: 1% !important;
  }
}
@media only screen and (max-width: 400px) {
  .td_first{
    width: 4% !important;
  }
  .td_main{
    width: 1% !important;
  }
  .td_last{
    width: 0.6% !important;
  }
}

@media only screen and (max-width: 350px) {
  .td_first{
    width: 3.8% !important;
  }
  .td_main{
    width: 0.5% !important;
  }
  .td_last{
    width: 0.6% !important;
  }
}
</style>
