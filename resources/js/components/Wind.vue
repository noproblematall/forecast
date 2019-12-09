<template>
    <div class="container custom_container">
      <section style="margin-top:10px;">
        <div class="row">
          <div class="col-12" style="text-align:center;">
            <p><i class="fa fa-clock-o"></i>&nbsp;&nbsp;&nbsp;Actualizada {{forecastDate | only_date_format}}</p>
          </div>
        </div>
        <div class="table-responsive tbl_wave_top" style="margin-top: 0px;">
          <table class="table">
            <thead>
              <tr>
                <th></th>
                <th>Dirección</th>
                <th>Velocidad</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in data_for_today" :key="index">
                <td>{{item['localDate'] | only_time_format}}</td>
                <td>
                  <div class="detail_data_wind">
                    <div class="row">
                      <div class="col-4">
                        <i class="fa fa-arrow-circle-up arrow" :style="get_rotate(item['winddir'])"></i>
                      </div>
                      <div class="col-8">
                        <div>{{toFixed_value(item['winddir'])}}º</div>
                        <div style="margin-top: -5px;">{{get_mark(item['winddir'])}}</div>
                      </div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="detail_data_wind">
                    <div class="row">
                      <div class="col-12" style="padding:0;margin-top:-5px;">
                        <span style="font-size:35px;">{{toFixed_value(item['windmag'])}}</span>
                        <span style="vertical-align: top;">kn</span>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
      <section class="sec_wind_two" style="margin-top:-20px;">
        <div class="row" style="margin-top: 95px;margin-bottom: -20px;">
                <div class="col-12 wave_unit">
                    <the-table v-if="scale_falg"></the-table>
                    <div class="altura">
                        <div style="font-weight:bolder"><span>Velocidad</span>&nbsp;&nbsp;<sup>kn</sup>&nbsp;&nbsp;<i class="fa fa-minus" style="color:#96b200;"></i></div>
                    </div>
                    <button class="btn btn-primary" @click="view_all_data()" v-if="view_all_data_flag">Vista General</button>
                    <button class="btn btn-primary" @click="view_detail_data()" v-else>Vista Detallada</button>

                    <div class="Direction">
                        <div><i class="fa fa-minus" style="color:#dc5c0d;"></i>&nbsp;&nbsp;<b>Dirección<sup>o</sup></b></div>
                    </div>
                </div>
        </div>
        <div class="row">

          <div class="col-12" v-if="loaded">
            <div>
                <apex-chart
                  ref="chart1"
                  height="470"
                  :options="options"
                  :series="chartdata_computed"
                ></apex-chart>
            </div>
          </div>

        </div>
      </section>
      <section style="margin-top:60px;" class="wind_third_section" v-if="view_all_data_flag">
        <table class="table tbl_detail">
          <thead style="border-bottom: 2px solid #737373;">
            <th>Fecha</th>
            <th></th>
            <th style="text-align:left;">Dir.</th>
            <th>Vel.<sup>kn</sup></th>
          </thead>
          <tbody>
            <tr v-for="(item, index) in data_for_table" :key="index">
              <td>{{item['localDate'] | date_time_format}}</td>
              <td style="text-align:right;"><i class="fa fa-arrow-up arrow" :style="get_rotate(item['winddir'])"></i></td>
              <td  style="text-align:left;">{{get_mark(item['winddir'])}}</td>
              <td>{{toFixed_value(item['windmag'])}}</td>
            </tr>
          </tbody>
        </table>
      </section>
      <section class="text-center">
        <p style="color: #96b200;">Descargar Pronóstico Viento</p>
        <div class="row">
          <div class="col-6">
            <button type="button" @click="export_xlsx()" class="btn btn-info btn_excel float-right"><span>Excel</span>&nbsp;<i class="fa fa-download"></i></button>
          </div>
          <div class="col-6">
            <button type="button" @click="export_csv()" class="btn btn-info btn_excel float-left"><span>CSV</span>&nbsp;<i class="fa fa-download"></i></button>
          </div>
        </div>
        <div class="clearfix"></div>
      </section>
    </div>
</template>

<script>
import VueApexCharts from 'vue-apexcharts';
import XLSX from 'xlsx';
import TheTable from './TheTable';
import moment from 'moment';
import { eventBus } from '../app';
    export default {
        props: {
          location: { // receive location value from App.vue component
            type: [String, Number],
            default: ''
          },
          date: { // receive date value from App.vue component
            type: [String, Number],
            default: '',
          }
        },
        data() {
            return{
                loaded: false,
                all_data: [],
                options: {},
                options_temp: {},
                chartdata: [],
                chart_data: [],
                chart_data_temp: [],
                chart_labels: [],
                chart_labels_temp: [],
                wind_dir: [], // array variable to save direction in tooltip of chart
                wind_dir_temp: [], // temp array variable for wind_dir
                base_url: '',
                uri: '',
                dir_id: [], 
                dir_mark: [],
                json_fields: {},
                forecastDate: '', // variable for forecast date
                temp_for_table: {}, // temp value for data_for_table variable
                data_for_table: [],
                range: [11.25,33.75,56.25,78.75,101.25,123.75,146.25,168.75,191.25,213.75,236.25,258.75,281.25,303.75,326.25,348.75],
                mark: ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'],
                temp_for_today: {}, // temp value for data_for_today
                data_for_today: [], // data for local date time
                all_date: [],  // include all date values when left and right arrow clicked
                temp_for_json: {}, // temp array for data_for_json
                data_for_json: [], // array for file download
                scale_falg: false,
                view_all_data_flag: true, // variable to check whether general view button clicked
                init_location: '',
                line_for_all_data_temp: {}, // temp array for line_for_all_data
                line_for_all_data: [], // array variable for every forecast date 00:00
                max_chart_data: 16,
                max_chart_data_temp: 16,
                location_name: ['punta_del_adcp', 'bocana', 'estacion_practicos'],
            }
        },
        methods: {
            init: function() { //get the all data from database
                try{
                    this.axios.get(this.uri).then(res => {
                        if(Object.keys(res.data).length > 0){
                          this.func_for_normal(res.data);

                        }
                      }
                    )
                }
                catch{

                }
            },
            date_format: function(value){// set the date format
              if (!value) return '';
              let date = [];
              date = value.split(' ')[1];
              date = date.split(':');
              return date[0] + 'h';
            },
            fetchData: function(){
              return [{}];
            },
            
            func_for_normal: function (result) { // get the all data from database and save in array variables.
              let vm = this;
              this.all_data = result.data;
              this.forecastDate = result.data[0].forecastDate;
              let k=0;
              for (let i = 0; i < this.all_data.length; i++) {
                if(this.all_data[i].nameId == 'windmag'){
                  this.chart_labels.push(this.all_data[i].localDate);
                  this.chart_data.push(this.all_data[i].value);
                  this.temp_for_table['windmag']=vm.all_data[i].value;
                  this.temp_for_table['localDate']=vm.all_data[i].localDate;
                }
                if(this.all_data[i].nameId == 'winddir'){
                  this.wind_dir.push(this.all_data[i].value);
                  this.temp_for_table['winddir']=vm.all_data[i].value;
                }
                if(k==4){
                  this.data_for_table.push(vm.temp_for_table);

                  this.temp_for_table = {};
                  k=0;
                  continue;
                }
                  k++;
              }

              let temp = 0;
              temp = vm.chart_data.reduce(function(a,b){
                return Math.max(a,b);
              });

              vm.max_chart_data = (temp < vm.max_chart_data) ? vm.max_chart_data : temp
              // define chart variables
              this.options = {
                annotations: {
                  yaxis: [
                    {
                      y: 10,
                      borderColor: "#d4c600",
                      strokeDashArray: 0,
                    },
                    {
                      y: 15,
                      borderColor: "#fb0707",
                      strokeDashArray: 0,
                    },
                  ],
                },
                tooltip: {
                  followCursor: true,
                  x: {
                      show: true,
                      formatter: function(val, index) {
                        let value = vm.chart_labels[val-1];
                        let date = [];
                        let time = [];
                        date = value.split(' ')[0];
                        time = value.split(' ')[1];
                        time = time.split(':');
                        time = time[0] + ':' + time[1];
                        date = date.split('-');
                        date = date[2] + '/' + date[1];
                        return date + ' ' + time;
                      }
                  },
                  y: [
                    {
                      formatter: function(y) {
                        return vm.toFixed_value(y);
                      }
                    },
                    {
                      formatter: function(y) {
                        return vm.get_mark(y);
                      },
                    }

                    ]
                  },

                yaxis: [
                    {

                    max: vm.max_chart_data,
                    min: 0,
                    show: true,
                    showAlways: true,
                    axisBorder: {
                      show: true,
                      color: '#78909C',
                      offsetX: 0,
                      offsetY: 0
                    },
                    axisTicks: {
                      show: true,
                      borderType: 'solid',
                      color: '#78909C',
                      width: 6,
                      offsetX: 0,
                      offsetY: 0
                    },
                    labels: {
                      style: {
                        color: "#000"
                      },
                      formatter: function(val, index) {
                        return val.toFixed(1);
                      }
                    },

                  },
                  {
                    opposite: true,
                    forceNiceScale: true,
                    max: 359,
                    min: 0,
                    axisTicks: {
                      show: true,
                      borderType: 'solid',
                      color: '#78909C',
                      width: 6,
                      offsetX: 0,
                      offsetY: 0
                    },
                    axisBorder: {
                      show: true,
                      color: '#e3342f',
                      offsetX: 0,
                      offsetY: 0
                    },
                    labels: {
                      style: {
                        color: "#000"
                      },
                      formatter: function(val, index) {
                        return val.toFixed(0);
                      }
                    },
                  }
                ],
                legend: {
                  show: false,
                },
                grid: {
                  show: true,
                  xaxis: {
                      lines: {
                          show: true
                      }
                  },
                  yaxis: {
                      lines: {
                          show: true
                      }
                  },
                },
                chart: {
                  toolbar:{
                    show:false,
                  }
                },
                  animations: {
                    enabled: true,
                    easing: 'linear',
                    dynamicAnimation: {
                        speed: 1000
                    }
                },
                colors: ["#96b200",'#e3342f'],
                stroke: {
                    show: true,
                    curve: ['smooth', 'smooth'],
                    lineCap: 'square',
                    colors: ['#96b200', '#96b200'],
                    width: [3, 3],
                },
                xaxis: {
                  categories: this.chart_labels,
                  labels: {
                    show: true,
                    trim: false,
                    formatter: function(value, timestamp, index) {
                      return vm.date_format(value);
                    }
                  },
                  axisTicks: {
                    show: true,
                  },
                  tooltip:{
                    enabled: false,
                  }

                },
                markers: {
                  size: [0, 5]
                },
                responsive: [
                  {
                    breakpoint: 900,
                    options: {
                      chart: {
                          height: 400
                      },
                    }
                  },
                  {
                    breakpoint: 650,
                    options: {
                      chart: {
                          height: 250
                      },
                    }
                  },
                  {
                    breakpoint: 450,
                    options: {
                      chart: {
                          height: 210,
                      },
                      xaxis: {
                        labels: {
                          style: {
                             fontSize: '10px',
                          }
                        },

                      }
                    }
                  }
                ]
              }
              this.loaded = true;
            },
            toFixed_value: function(value){ // change the values to float type
              return parseFloat(value).toFixed(1);
            },
            get_mark: function(value){ // change the letter(N,W...) correspond to direction values(0~360)
              for (let i = 0; i < this.range.length; i++) {
                if(value < this.range[i]){
                  return this.mark[i];
                }
              }
              return '-';
            },
            get_rotate: function(value){// rotate the arrow icon according to direction 
              let rotate = value - 180;
              return 'transform: rotate('+ rotate +'deg);';
            },
            get_current_data: function(params = '',callback){ // get current local data
              let currentObject = this;
              let today = this.moment().format('YYYY-MM-DD HH:mm:ss');
              let location_id = params == '' ? 1 : params
              this.axios.post(this.base_url + 'api/get_today_data', {
                  today: today,
                  location_id: location_id
              })
              .then(function (res) {
                  if(Object.keys(res.data).length > 0){
                    currentObject.init_location = currentObject.$attrs.init_location;
                    let j = 0;
                    for (let i = 0; i < res.data.today_data.length; i++) {
                      if(res.data.today_data[i].nameId == 'winddir'){
                        currentObject.temp_for_today['localDate']=res.data.today_data[i].localDate;
                        currentObject.temp_for_today['winddir']=res.data.today_data[i].value;
                      }
                      if(res.data.today_data[i].nameId == 'windmag'){
                        currentObject.temp_for_today['windmag']=res.data.today_data[i].value;
                      }

                      if(j==4){
                        currentObject.data_for_today.push(currentObject.temp_for_today);
                        currentObject.temp_for_today = {};
                        j=0;
                        continue;
                      }
                        j++;
                    }
                    currentObject.all_date = res.data.dates;
                    currentObject.excelData();
                    callback();
                  }
              })
              .catch(function (error) {
              });
            },
            moment: function(){// return date control object
              return moment();
            },
            excelData: async function(){ // get the all data to download excel file
              let vm = this;
              let uri = this.base_url + 'api/get_json';
              let j = 0;
              await this.axios.post(uri, {
                  'locationId': (vm.location=='' ? 1 : vm.location),
              })
              .then(function(res){
                if(Object.keys(res.data).length > 0){
                  for (let i = 0; i < res.data.length; i++) {
                    vm.temp_for_json['Tiempo Local']=vm.date_time_excel(res.data[i].localDate);
                    if(res.data[i].nameId == 'winddir'){
                      vm.temp_for_json['WindDir [°]']=vm.get_mark(res.data[i].value);
                    }
                    if(res.data[i].nameId == 'windmag'){
                      vm.temp_for_json['WindMag [m]']=vm.toFixed_value(res.data[i].value);
                    }

                    if(j==4){
                      let data_for_order = {}
                      data_for_order['Tiempo Local'] = vm.temp_for_json['Tiempo Local']
                      data_for_order['WindDir [°]'] = vm.temp_for_json['WindDir [°]']
                      data_for_order['WindMag [m]'] = vm.temp_for_json['WindMag [m]']

                      vm.data_for_json.push(data_for_order);

                      vm.temp_for_json = {};
                      j=0;
                      continue;
                    }
                      j++;
                  }
                }
              })
            },
            export_xlsx: function(){// export the excel file
              var windWS = XLSX.utils.json_to_sheet(this.data_for_json)
              var wb = XLSX.utils.book_new()
              XLSX.utils.book_append_sheet(wb, windWS, 'wind')
              let location = this.location == '' ? 1 : this.location
              location = this.location_name[parseInt(location) - 1]
              let file_name = 'wind_' + location + '.xlsx'
              XLSX.writeFile(wb, file_name)
            },
            export_csv: function() {// export the csv file
              const wb = XLSX.utils.book_new()
              const ws = XLSX.utils.json_to_sheet(this.data_for_json)
              XLSX.utils.book_append_sheet(wb, ws, 'wind')
              let location = this.location == '' ? 1 : this.location
              location = this.location_name[parseInt(location) - 1]
              let file_name = 'wind_' + location + '.csv'
              XLSX.writeFile(wb, file_name)
            },
            date_time_excel: function(value){ // get the local time field data to export excel file
              if (!value) return '';
              let date = [];
              let time = [];
              date = value.split(' ')[0];
              time = value.split(' ')[1];
              time = time.split(':');
              time = time[0] + ':' + time[1];
              date = date.split('-');
              date = date[2] + '/' + date[1] + '/' + date[0];
              return date + ' ' + time;
            },
            get_for_location: function (id, start_date='',end_date='') {// get the location data from 
              let vm = this;
              this.axios.post(this.base_url + 'api/get_for_location', {
                  'locationId': id,
                  'start_date': start_date,
                  'end_date' : end_date
              })
              .then(function (res) {
                if(Object.keys(res.data).length > 0){
                  vm.func_for_normal(res.data);
                }
              })
              .catch(function (error) {
              });
            },
            startDownload: function(){
            },
            finishDownload: function(){
            },
            view_all_data: function() {// get the all data for general chart from database
                eventBus.viewAllData();
                this.view_all_data_flag = !this.view_all_data_flag
                this.chart_data_temp = this.chart_data
                this.chart_labels_temp = this.chart_labels
                this.wind_dir_temp = this.wind_dir
                this.chart_data = []
                this.chart_labels = []
                this.wind_dir = []
                this.options_temp = this.options
                this.options = {}
                this.max_chart_data_temp = this.max_chart_data
                let vm = this;
                let id = this.location ? this.location : this.init_location
                this.axios.post(this.base_url + 'api/get_all', {
                    'locationId': id,
                })
                .then(function (res) {
                    if(Object.keys(res.data).length > 0){
                        let data = res.data.data
                        for (let i = 0; i < data.length; i++) {
                            if(data[i].nameId == 'windmag'){
                                vm.chart_labels.push(data[i].localDate);
                                vm.chart_data.push(data[i].value);
                            }
                            if(data[i].nameId == 'winddir'){
                                vm.wind_dir.push(data[i].value);
                            }
                        }

                        let temp = 0;
                        temp = vm.chart_data.reduce(function(a,b){
                            return Math.max(a,b);
                        });

                        vm.max_chart_data = (temp < vm.max_chart_data) ? vm.max_chart_data : temp

                        for (let i = 0; i < vm.chart_labels.length; i++) {
                          let time = [];
                          time = vm.chart_labels[i].split(' ')[1];
                          time = time.split(':');
                          if(time[0] == '00'){
                            vm.line_for_all_data_temp['x'] = i + 1
                            vm.line_for_all_data_temp['borderColor'] = '#e0e0e0'
                            vm.line_for_all_data_temp['strokeDashArray'] = 0
                            vm.line_for_all_data.push(vm.line_for_all_data_temp)
                            vm.line_for_all_data_temp = {}
                          }
                        }
                        vm.options = {
                            annotations: {
                                position: 'back' ,
                                xaxis: vm.line_for_all_data
                            },
                            yaxis: [
                                    {

                                    max: vm.max_chart_data,
                                    min: 0,
                                    show: true,
                                    showAlways: true,
                                    axisBorder: {
                                    show: true,
                                    color: '#78909C',
                                    offsetX: 0,
                                    offsetY: 0
                                    },
                                    axisTicks: {
                                    show: true,
                                    borderType: 'solid',
                                    color: '#78909C',
                                    width: 6,
                                    offsetX: 0,
                                    offsetY: 0
                                    },
                                    labels: {
                                    style: {
                                        color: "#000"
                                    },
                                    formatter: function(val, index) {
                                        return val.toFixed(1);
                                    }
                                    },

                                },
                                {
                                    opposite: true,
                                    forceNiceScale: true,
                                    max: 359,
                                    min: 0,
                                    axisTicks: {
                                    show: true,
                                    borderType: 'solid',
                                    color: '#78909C',
                                    width: 6,
                                    offsetX: 0,
                                    offsetY: 0
                                    },
                                    axisBorder: {
                                    show: true,
                                    color: '#e3342f',
                                    offsetX: 0,
                                    offsetY: 0
                                    },
                                    labels: {
                                    style: {
                                        color: "#000"
                                    },
                                    formatter: function(val, index) {
                                        return val.toFixed(0);
                                    }
                                    },
                                }
                                ],
                            xaxis: {
                                categories: vm.chart_labels,
                                labels: {
                                    show: true,
                                    hideOverlappingLabels: false,
                                    trim: false,
                                    rotate: 0,
                                    minHeight: 20,
                                    formatter: function(value, timestamp, index) {


                                        if(value == undefined){
                                            return ''
                                        }else{
                                            let date = [];
                                            let time = [];
                                            date = value.split(' ')[0];
                                            date = date.split('-');
                                            time = value.split(' ')[1];
                                            time = time.split(':');
                                            if(time[0] == '00'){
                                                return date[2]
                                            }else{
                                                return ''
                                            }
                                        }

                                        for (let i = 0; i < vm.all_date.length; i++) {
                                            if(value == vm.all_date[i]){
                                                let date = [];
                                                date = value.split(' ')[0];
                                                date = date.split('-');
                                                return date[2] + '/' + date[1];
                                            }else {
                                                if(i == vm.all_date.length-1){
                                                    value = ''
                                                }
                                            }
                                        }
                                        return value;
                                    }
                                },
                                axisTicks: {
                                    show: false,
                                },

                            },
                            grid: {
                                xaxis: {
                                    lines: {
                                        show: false
                                    }
                                },
                            },
                            markers: {
                                size: [0, 3]
                            },
                        }
                        vm.$refs.chart1.updateOptions(vm.options)
                    }
                })
                .catch(function (error) {
                });

            },
            view_detail_data: function() {// get the all data for detail chart from database once it changed from general chart to detail chart
                this.view_all_data_flag = !this.view_all_data_flag
                this.chart_data = this.chart_data_temp
                this.chart_labels = this.chart_labels_temp
                this.wind_dir = this.wind_dir_temp
                this.options = this.options_temp
                this.chart_data_temp = []
                this.chart_labels_temp = []
                this.wind_dir_temp = []
                this.options_temp = {}
                this.max_chart_data = this.max_chart_data_temp
                this.$refs.chart1.updateOptions(this.options)
                eventBus.detailData();
            }
        },
        computed: {
            chartdata_computed() { // get the data for tooltip of chart
                return this.chartdata = [
                            {
                            name: 'Velocidad',
                            type: 'line',
                            data: this.chart_data
                            },
                            {
                            name: 'Dirección',
                            type: 'scatter',
                            data: this.wind_dir
                            }
                        ];
            }
        },
        filters: {
          date_time_format: function(value){// get the filtered time from database.
            if (!value) return '';
            let date = [];
            let time = [];
            date = value.split(' ')[0];
            time = value.split(' ')[1];
            time = time.split(':');
            time = time[0] + ':' + time[1];
            date = date.split('-');
            date = date[2] + '/' + date[1];
            return date + ' | ' + time;
          },
          only_date_format: function(value){// get the filtered date from database.
            if (!value) return '';
            let date = [];
            date = value.split(' ')[0];
            date = date.split('-');
            return date[2] + '/' + date[1] + '/' + date[0];
          },
          only_time_format: function(value){// get the only time value from database.
            if (!value) return '';
            let time=[];
            time = value.split(' ')[1];
            time = time.split(':');
            return time[0] + ':' + time[1];
          },

        },
        async mounted () {// once page load, first of all called(vue default function)
          let vm = this;
          this.loaded = false;
          this.base_url = document.getElementById('base_url').value;
          this.uri = this.base_url + 'api/init_wave';
          let start_date = '';
          let end_date = '';
          if(this.location == '' && this.date == ''){
            this.init();
            this.get_current_data('',function(){});
          }else if(this.location != '' && this.date == 'start'){
            this.get_current_data(this.location,function(){});
            this.get_for_location(this.location);
          }else if(this.date != 'start'){
            let temp = this.date.split('_');
            if(temp[0] == 'left'){
              this.get_current_data(this.location ,function () {
                start_date = vm.all_date[parseInt(temp[1])-1];
                end_date = vm.all_date[parseInt(temp[1])];
                vm.get_for_location(vm.location, start_date, end_date);
              });
            }else if(temp[0] == 'right'){
              this.get_current_data(this.location, function () {
                start_date = vm.all_date[parseInt(temp[1])];
                end_date = vm.all_date[parseInt(temp[1])+1];
                vm.get_for_location(vm.location, start_date, end_date);

              });
            }
          }
        },
        components: {
            apexChart: VueApexCharts,
            theTable: TheTable,
        }
    }
</script>

<style scoped>
  .scale img {
        top: -50px;
        left: 145px;
        z-index: 10;
  }
  .wave_unit button {
        position: absolute;
        top: -44px;
        left: calc(50% - 52px);
  }
</style>
