<template>
    <div class="container" style="margin-top:85px;">
      <section style="margin-top:10px;">
        <div class="row">
          <div class="col-12" style="text-align:center;">
            <p><i class="fa fa-clock-o"></i>&nbsp;&nbsp;&nbsp;Actualizada&nbsp;&nbsp;{{forecastDate | only_date_format}}</p>
          </div>
        </div>
        <div class="table-responsive tbl_wave_top" style="margin-top: 0px;">
          <table class="table">
            <thead>
              <tr>
                <th></th>
                <th><b>Dirección&nbsp;&nbsp;<sup>o</sup></b></th>
                <th><span>Altura(Hm)<sub>0</sub></span></th>
                <th><span>Periodo</span></th>
              </tr>
            </thead>
            <tbody>

              <tr v-for="(item, index) in data_for_today" :key="index">
                <td>{{item['localDate'] | only_time_format}}</td>
                <td>
                  <div class="detail_data">
                    <div class="row">
                      <div class="col-4">
                        <i class="fa fa-arrow-circle-up arrow" :style="get_rotate(item['dir'])"></i>
                      </div>
                      <div class="col-8">
                        <div>{{toFixed_value(item['dir'])}}º</div>
                        <div style="margin-top: -5px;">{{get_mark(item['dir'])}}</div>
                      </div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="detail_data">
                    <div class="row">
                      <div class="col-12" style="padding:0;margin-top:-5px;">
                        <span style="font-size:35px;">{{toFixed_value(item['hm0'])}}</span>
                        <span style="vertical-align: top;">m</span>
                      </div>
                    </div>
                  </div>
                </td>
                <td>
                  <div class="detail_data">
                    <div class="row">
                      <div class="col-12" style="padding:0;margin-top:-5px;">
                        <span style="font-size:35px;">{{toFixed_value(item['tp'])}}</span>
                        <span style="vertical-align: top;">s</span>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>

            </tbody>
          </table>
        </div>
      </section>
      <section class="sec_wave_two">
          <div class="row" style="margin-bottom: 0px; margin-top: 50px;">
              <div class="col-12 y_responsive">
                      <the-table v-if="scale_falg"></the-table>
                      <div class="altura">
                        <div style="font-weight:bolder"><span>Altura</span>&nbsp;&nbsp;<sup>m</sup></div>
                        <div style="margin-top: -5px;">(Hm)<sub>0</sub>&nbsp;&nbsp;<i class="fa fa-minus" style="color:#247ade;"></i></div>
                      </div>
                      <div class="scale">
                          <img src="/img/button.jpg" alt="Button" @click="scale_falg = !scale_falg" srcset="">
                      </div>
                      <button class="btn btn-primary" @click="view_all_data()" v-if="view_all_data_flag">Vista General</button>
                      <button class="btn btn-primary" @click="view_detail_data()" v-else>Vista Detallada</button>
                      <div class="Periodo">
                        <div style="font-weight:bolder"><span >Periodo</span>&nbsp;&nbsp;<sup>s</sup></div>
                        <div style="margin-top: -5px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i class="fa fa-minus" style="color:#f98d02;"></i>&nbsp;&nbsp;(Tp)</div>
                      </div>
                      <div class="Direction">
                        <div><b>Dir<sup>o</sup></b></div>
                        <div style="margin-top: -5px;">&nbsp;&nbsp;<i class="fa fa-minus" style="color:#dc5c0d;"></i>&nbsp;&nbsp;(DirTp)</div>
                      </div>
              </div>
          </div>
          <div class="row">
             <div class="col-12" >
                <div class="chart_above_div">
                    <apex-chart
                      ref="chart1"
                      height=470
                      v-if="loaded"
                      :series="this.chartdata_multi_computed"
                      :options="multi_options"

                    ></apex-chart>
                </div>
             </div>
          </div>
      </section>
      <section style="margin-top:0px;" v-if="view_all_data_flag">
        <table class="table">
          <thead style="border-bottom: 2px solid #737373;">
            <th>Fecha</th>
            <th>DirTpº</th>
            <th>Hm<sub>0</sub>&nbsp;&nbsp;<sup>m</sup></th>
            <th>Tp&nbsp;&nbsp;<sup>s</sup></th>
          </thead>
          <tbody>
            <tr v-for="(item, index) in data_for_table" :key="index">
              <td>{{item['localDate'] | date_time_format}}</td>
              <td><i class="fa fa-arrow-up arrow" :style="get_rotate(item['dir'])"></i>&nbsp;&nbsp;&nbsp;{{toFixed_value(item['dir'])}}</td>
              <td>{{toFixed_value(item['hm0'])}}</td>
              <td>{{toFixed_value(item['tp'])}}</td>
            </tr>
          </tbody>
        </table>
      </section>
      <section class="text-center">
        <p style="color: #96b200;">Descargar Pronostico Oleaje</p>
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
import moment from 'moment';
import TheTable from './TheTable';
import { eventBus } from '../app';
    export default {
        props: {
          location: {   // receive location value from App.vue component
            type: [String, Number],
            default: ''
          },
          date: {  // receive date value from App.vue component
            type: [String, Number],
            default: '',
          }
        },
        data() {
            return{
                loaded: false,
                normal_options: {},
                multi_options: {},
                multi_options_temp: {},
                chartdata_multi: [], //  array variable for tooltip of chart
                chartdata_normal: [], 
                chart_data_normal: [], // array variable to save direction value of chart
                chart_data_multi_left: [], // array variable to save altura value of chart
                chart_data_multi_right: [], // array variable to save periodo value of chart
                chart_data_normal_temp: [], // temp array variable for chart_data_normal
                chart_data_multi_left_temp: [], // temp array variable for chart_data_multi_left
                chart_data_multi_right_temp: [], // temp array variable for chart_data_multi_right
                chart_labels_normal: [], // array variable to save xaxis lable of chart
                chart_labels_normal_temp: [], // temp array variable for chart_labels_normal
                base_url: '',
                uri: '',
                all_data: [],  // include all field in meature table of database
                data_for_table: [],  // include data for below table
                temp_for_table: {},  // temp value for data_for_table variable
                forecastDate: '',    // variable for forecast date
                data_for_today: [],  // data for local date time
                temp_for_today: {},  // temp value for data_for_today
                range: [11.25,33.75,56.25,78.75,101.25,123.75,146.25,168.75,191.25,213.75,236.25,258.75,281.25,303.75,326.25,348.75],  // data for wave direction
                mark: ['N','NNE','NE','ENE','E','ESE','SE','SSE','S','SSW','SW','WSW','W','WNW','NW','NNW'], // data mark for wave direction
                wave_range: [0,0.1,0.5,1.25,2.5,4,6,9,14,1000],
                wave_mark: ['Mar llana o en calma','Mar rizada','Marejadilla','Marejada','Fuerte marejada','Gruesa','Muy gruesa','Arbolada','Montanosa','Enoreme'],
                init_location: '',
                all_date: [],  // include all date values when left and right arrow clicked
                data_for_json: [], // array for file download
                temp_for_json: {}, // temp array for data_for_json
                scale_falg: false, // variable to check whether button for Dogulas scale table clicked
                view_all_data_flag: true, // variable to check whether general view button clicked
                line_for_all_data: [], // array variable for every forecast date 00:00
                line_for_all_data_temp: {}, // temp array for line_for_all_data
                max_altura: 2.5, // altura basic max value
                max_altura_temp: 2.5, // temp value for max_altura
                location_name: ['punta_del_adcp', 'bocana', 'estacion_practicos'],
            }
        },
        methods: {
            init: function() {//get the all data from database
                let currentObject = this;
                this.uri = this.base_url + 'api/init_wave';
                try {
                    this.axios.get(this.uri).then(res => {
                        if(Object.keys(res.data).length > 0){
                            this.func_for_normal(res.data);

                        }
                    }).catch(function (error) {
                        if(error != ''){
                        }

                    })
                    .finally(function () {
                    });
                } catch (e) {
                }
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
                      if(res.data.today_data[i].nameId == 'dir'){
                        currentObject.temp_for_today['localDate']=res.data.today_data[i].localDate;
                        currentObject.temp_for_today['dir']=res.data.today_data[i].value;
                      }
                      if(res.data.today_data[i].nameId == 'hm0'){
                        currentObject.temp_for_today['hm0']=res.data.today_data[i].value;
                      }
                      if(res.data.today_data[i].nameId == 'tp'){
                        currentObject.temp_for_today['tp']=res.data.today_data[i].value;
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
            get_for_location: function (id, start_date='',end_date='') { // get the location data from 
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
            date_format: function(value){// set the date format
              if (!value) return '';
              let date = [];
              date = value.split(' ')[1];
              date = date.split(':');
              return date[0] + 'h';
            },
            func_for_normal: function(result) {// get the all data from database and save in array variables.
              let currentObject = this;
              let vm = this;
              this.all_data = result.data;
              this.forecastDate = result.data[0].forecastDate;
              let j = 0;
              for (let i = 0; i < this.all_data.length; i++) {
                if(this.all_data[i].nameId == 'dir'){
                  this.chart_labels_normal.push(this.all_data[i].localDate);
                  this.chart_data_normal.push(this.all_data[i].value);
                  this.temp_for_table['localDate']=currentObject.all_data[i].localDate;
                  this.temp_for_table['dir']=currentObject.all_data[i].value;
                }
                if(this.all_data[i].nameId == 'hm0'){
                  this.chart_data_multi_left.push(this.toFixed_value(this.all_data[i].value));
                  this.temp_for_table['hm0']=currentObject.all_data[i].value;
                }
                if(this.all_data[i].nameId == 'tp'){
                  this.chart_data_multi_right.push(this.toFixed_value(this.all_data[i].value));
                  this.temp_for_table['tp']=currentObject.all_data[i].value;
                }

                if(j==4){
                  this.data_for_table.push(currentObject.temp_for_table);

                  this.temp_for_table = {};
                  j=0;
                  continue;
                }
                  j++;
              }

              let temp = 0;
              temp = vm.chart_data_multi_left.reduce(function(a,b){
                return Math.max(a,b);
              });
                vm.max_altura = (temp < vm.max_altura ? vm.max_altura : (temp+1))



              this.multi_options = {

                annotations: {
                  yaxis: [
                    {
                      y: 1.5,
                      borderColor: "#00a96c",
                      strokeDashArray: 0,
                    },
                    {
                      y: 2,
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
                        let value = vm.chart_labels_normal[val-1];
                        let wave = vm.chart_data_multi_left[val-1];
                        let wave_tooltip = vm.get_wave(wave)
                        let date = [];
                        let time = [];
                        date = value.split(' ')[0];
                        time = value.split(' ')[1];
                        time = time.split(':');
                        time = time[0] + ':' + time[1];
                        date = date.split('-');
                        date = date[2] + '/' + date[1];
                        return date + ' ' + time + '<br />' + '(' + wave_tooltip + ')';
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
                                return vm.toFixed_value(y);
                            },
                        },
                        {
                            formatter: function(y) {
                                return y
                            },
                        },

                    ]
                },
                yaxis: [
                  {
                    max: vm.max_altura,
                    axisTicks: {
                      show: true
                    },
                    axisBorder: {
                      show: true,
                      color: "#0269e0"
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
                    axisTicks: {
                      show: true
                    },
                    axisBorder: {
                      show: true,
                      color: "#f98d02"
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
                colors: ["#0269e0", "#f98d02",'#e3342f'],
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
                stroke: {
                    show: true,
                    curve: ['smooth','smooth','smooth'],
                    colors: ['#0269e0','#f98d02','#e3342f'],
                    width: [3,3,3],
                },
                xaxis: {
                  categories: this.chart_labels_normal,
                  tickAmount: 5,
                  labels: {
                    show: true,
                    trim: false,
                    formatter: function(value, timestamp, index) {
                      return vm.date_format(value);
                    }
                  },
                  tooltip: {
                    enabled: false,
                  }
                },
                markers: {
                    size: [0, 0, 5]
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
                      yaxis: [
                        {
                          max: vm.max_altura,
                          axisTicks: {
                            show: true
                          },
                          axisBorder: {
                            show: true,
                            color: "#0269e0"
                          },
                          labels: {
                            style: {
                              color: "#000",
                              fontSize: '10px'
                            },
                            formatter: function(val, index) {
                              return val.toFixed(1);
                            }
                          },
                        },
                        {
                          opposite: true,
                          axisTicks: {
                            show: true
                          },
                          axisBorder: {
                            show: true,
                            color: "#f98d02"
                          },
                          labels: {
                            style: {
                              color: "#000",
                              fontSize: '10px'
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
                            offsetX: 10,
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
                              color: "#000",
                              fontSize: '9px'
                            },
                            offsetX: 17,
                            offsetY: 0,
                            formatter: function(val, index) {
                              return val.toFixed(0);
                            }
                          },
                          axisTicks: {
                            show: true,
                            width: 4,
                        }
                        }
                      ],
                      xaxis: {
                        labels: {
                          style: {
                             fontSize: '10px',
                          }
                        },
                        tooltip: {
                          enabled: false,
                        }
                      }

                    }
                  }
                ]
              }
            
              this.loaded = true;
            },
            moment: function(){// return date process object
              return moment();
            },
            toFixed_value: function(value){// change the values to float type
              return parseFloat(value).toFixed(1);
            },
            get_mark: function(value){// change the letter(N,W...) correspond to direction values(0~360)
              for (let i = 0; i < this.range.length; i++) {
                if(value < this.range[i]){
                  return this.mark[i];
                }
              }
              return '-';
            },
            get_wave: function(value){// change the letter(N,W...) correspond to wave direction values(0~360)
              for (let i = 0; i < this.wave_range.length; i++) {
                if(value == 0){
                    return this.wave_mark[0]
                }
                else if(value < this.wave_range[i]){
                  return this.wave_mark[i];
                }
              }
            },
            get_rotate: function(value){// rotate the arrow icon according to direction 
              let rotate = value - 180;
              return 'transform: rotate('+ rotate +'deg);';
            },
            excelData: async function(){// get the all data to download excel file
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
                    if(res.data[i].nameId == 'dir'){
                      vm.temp_for_json['Dir [°]']=res.data[i].value;
                    }
                    if(res.data[i].nameId == 'hm0'){
                      vm.temp_for_json['Hm0 [m]']=vm.toFixed_value(res.data[i].value);
                    }
                    if(res.data[i].nameId == 'tp'){
                      vm.temp_for_json['Tp [s]']=vm.toFixed_value(res.data[i].value);
                    }
                    if(j==4){
                      let data_for_order = {}
                      data_for_order['Tiempo Local'] = vm.temp_for_json['Tiempo Local']
                      data_for_order['Dir [°]'] = vm.temp_for_json['Dir [°]']
                      data_for_order['Hm0 [m]'] = vm.temp_for_json['Hm0 [m]']
                      data_for_order['Tp [s]'] = vm.temp_for_json['Tp [s]']
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
              var waveWS = XLSX.utils.json_to_sheet(this.data_for_json)
              var wb = XLSX.utils.book_new()
              XLSX.utils.book_append_sheet(wb, waveWS, 'wave')
              let location = this.location == '' ? 1 : this.location
              location = this.location_name[parseInt(location) - 1]
              let file_name = 'wave_' + location + '.xlsx'
              XLSX.writeFile(wb, file_name)
            },
            export_csv: function() {// export the csv file
              const wb = XLSX.utils.book_new()
              const ws = XLSX.utils.json_to_sheet(this.data_for_json)
              XLSX.utils.book_append_sheet(wb, ws, 'wave')
              let location = this.location == '' ? 1 : this.location
              location = this.location_name[parseInt(location) - 1]
              let file_name = 'wave_' + location + '.csv'
              XLSX.writeFile(wb, file_name)
            },
            date_time_excel: function(value){// get the local time field data to export excel file
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
            view_all_data: function() {// get the all data for general chart from database
                eventBus.viewAllData();
                this.view_all_data_flag = !this.view_all_data_flag
                this.chart_data_multi_left_temp = this.chart_data_multi_left
                this.chart_data_multi_right_temp = this.chart_data_multi_right
                this.chart_data_normal_temp = this.chart_data_normal
                this.chart_labels_normal_temp = this.chart_labels_normal
                this.chart_labels_normal = []
                this.chart_data_multi_left = []
                this.chart_data_multi_right = []
                this.chart_data_normal = []
                this.multi_options_temp = this.multi_options
                this.multi_options = {}
                this.max_altura_temp = this.max_altura
                let vm = this;
                let id = this.location ? this.location : this.init_location
                this.axios.post(this.base_url + 'api/get_all', {
                    'locationId': id,
                })
                .then(function (res) {
                    if(Object.keys(res.data).length > 0){
                        let data = res.data.data

                        for (let i = 0; i < data.length; i++) {
                            if(data[i].nameId == 'dir'){

                                vm.chart_labels_normal.push(data[i].localDate);
                                vm.chart_data_normal.push(data[i].value);
                            }
                            if(data[i].nameId == 'hm0'){
                                vm.chart_data_multi_left.push(vm.toFixed_value(data[i].value));
                            }
                            if(data[i].nameId == 'tp'){
                                vm.chart_data_multi_right.push(vm.toFixed_value(data[i].value));
                            }
                        }

                        let temp = 0;
                        temp = vm.chart_data_multi_left.reduce(function(a,b){
                            return Math.max(a,b);
                        });
                            vm.max_altura = (temp < vm.max_altura ? vm.max_altura : (temp))

                        for (let i = 0; i < vm.chart_labels_normal.length; i++) {
                          let time = [];
                          time = vm.chart_labels_normal[i].split(' ')[1];
                          time = time.split(':');
                          if(time[0] == '00'){
                            vm.line_for_all_data_temp['x'] = i + 1
                            vm.line_for_all_data_temp['borderColor'] = '#e0e0e0'
                            vm.line_for_all_data_temp['strokeDashArray'] = 0
                            vm.line_for_all_data.push(vm.line_for_all_data_temp)
                            vm.line_for_all_data_temp = {}
                          }
                        }

                        vm.multi_options = {
                            annotations: {
                                position: 'back' ,
                                xaxis: vm.line_for_all_data
                            },
                            yaxis: [
                                {
                                    max: vm.max_altura,
                                    axisTicks: {
                                    show: true
                                    },
                                    axisBorder: {
                                    show: true,
                                    color: "#0269e0"
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
                                    axisTicks: {
                                    show: true
                                    },
                                    axisBorder: {
                                    show: true,
                                    color: "#f98d02"
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
                                categories: vm.chart_labels_normal,
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
                                size: [0, 0, 3]
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
                                yaxis: [
                                  {
                                    max: vm.max_altura,
                                    axisTicks: {
                                      show: true
                                    },
                                    axisBorder: {
                                      show: true,
                                      color: "#0269e0"
                                    },
                                    labels: {
                                      style: {
                                        color: "#000",
                                        fontSize: '10px'
                                      },
                                      formatter: function(val, index) {
                                        return val.toFixed(1);
                                      }
                                    },
                                  },
                                  {
                                    opposite: true,
                                    axisTicks: {
                                      show: true
                                    },
                                    axisBorder: {
                                      show: true,
                                      color: "#f98d02"
                                    },
                                    labels: {
                                      style: {
                                        color: "#000",
                                        fontSize: '10px'
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
                                      offsetX: 10,
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
                                        color: "#000",
                                        fontSize: '9px'
                                      },
                                      offsetX: 17,
                                      offsetY: 0,
                                      formatter: function(val, index) {
                                        return val.toFixed(0);
                                      }
                                    },
                                    axisTicks: {
                                      show: true,
                                      width: 4,
                                  }
                                  }
                                ],
                                xaxis: {
                                  labels: {
                                    style: {
                                      fontSize: '10px',
                                    }
                                  },
                                  tooltip: {
                                    enabled: false,
                                  }
                                }

                              }
                            }
                          ]
                        }
                        vm.$refs.chart1.updateOptions(vm.multi_options)
                    }
                })
                .catch(function (error) {
                });

            },
            view_detail_data: function() {// get the all data for detail chart from database once it changed from general chart to detail chart
                this.view_all_data_flag = !this.view_all_data_flag
                this.chart_data_multi_left = this.chart_data_multi_left_temp
                this.chart_data_multi_right = this.chart_data_multi_right_temp
                this.chart_data_normal = this.chart_data_normal_temp
                this.multi_options = this.multi_options_temp
                this.chart_labels_normal = this.chart_labels_normal_temp
                this.multi_options_temp = {}
                this.max_altura = this.max_altura_temp
                this.$refs.chart1.updateOptions(this.multi_options)
                eventBus.detailData();
            }
        },
        computed: {
            chartdata_multi_computed() {  // get the data for tooltip of chart
                return this.chartdata_multi = [
                        {
                            name: "Hm0",
                            type: 'line',
                            data: this.chart_data_multi_left
                        },
                        {
                            name: "Tp",
                            type: 'line',
                            data: this.chart_data_multi_right
                        },
                        {
                            name: 'Dir',
                            type: 'scatter',
                            data: this.chart_data_normal
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
          }
        },
        async mounted () {// once page load, first of all called(vue default function)
          let vm = this;
          let start_date = '';
          let end_date = '';
          this.loaded = false;
          this.base_url = document.getElementById('base_url').value;
          if(this.location == '' && this.date == ''){
            this.init();
            this.get_current_data('',function(){});
          }else if(this.location != '' && this.date == 'start'){
            this.get_current_data(this.location, function(){});
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
        created() {
        },
        components: {
            apexChart: VueApexCharts,
            theTable: TheTable,
        }
    }
</script>


<style scoped>
.table tr td i {
  color:#4990e2;
}
.y_responsive button {
    position: absolute;
    top: -30px;
    left: calc(50% - 52px);
    background-color: #1fbbeb;
    border: none;
}
@media (max-width: 600px){
    .y_responsive {
        padding: 0;
        padding-left: 25px;
    }
}
</style>

