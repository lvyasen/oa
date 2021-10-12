webpackJsonp([21],{"7TWH":function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var i=a("ryJa"),s=a.n(i),l={components:{NavTemplent:a("15QY").a},data:function(){return{listData:[],statisticalList:[],getDepartmentHead:[],departmentList:[],dateList:[],valueList:[],page:{pageSize:1,pageNum:50},dialogVisible:!1,total:0,title:"查看部门负责人详情",form:{cycle:0,date:[s()(s()().valueOf()-6048e5).format("YYYY/MM/DD"),s()(s()()+864e5).format("YYYY/MM/DD")],department_id:""}}},methods:{drawLine:function(){this.$echarts.init(document.getElementById("myChart")).setOption({title:{text:"各部门指标数据"},xAxis:{type:"category",boundaryGap:!1,data:this.dateList},yAxis:{type:"value"},series:[{data:this.valueList,type:"line",areaStyle:{}}]})},getStatisticalList:function(){var t=this;this.startTime=this.$root.$options.filters.dateFormat(this.form.date[0],"YYYY/MM/DD"),this.lastTime=this.$root.$options.filters.dateFormat(this.form.date[1],"YYYY/MM/DD"),this.$post("getDepartmentQuotaList",{start_time:this.startTime,end_time:this.lastTime,department_id:this.form.department_id}).then(function(e){t.statisticalList=e})},openDetails:function(t){var e=this;this.dialogVisible=!0,this.startTime=this.$root.$options.filters.dateFormat(this.form.date[0],"YYYY/MM/DD"),this.lastTime=this.$root.$options.filters.dateFormat(this.form.date[1],"YYYY/MM/DD"),this.$post("getDepartmentQuotaDetail",{start_time:this.startTime,end_time:this.lastTime,department_id:t.department_id}).then(function(t){e.getDepartmentHead=t.list,e.dateList=t.quota_chart.map(function(t){return t.date}),e.valueList=t.quota_chart.map(function(t){return t.value}),t.quota_chart&&e.$nextTick(function(){e.drawLine()})})},cancle:function(){this.dialogVisible=!1,this.$emit("close")},getDepartmentList:function(){var t=this;this.$post("/getDepartmentList").then(function(e){t.departmentList=e.list}).catch(function(e){t.$message.error(e)})}},mounted:function(){this.getStatisticalList(),this.getDepartmentList()}},n={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",[a("NavTemplent",{attrs:{title:"部门负责人指标库"}}),t._v(" "),a("div",{staticStyle:{padding:"15px 10% 15px 15px"}},[a("div",[a("h5",{staticStyle:{"font-weight":"bold"}},[t._v("数据筛选")]),t._v(" "),a("el-form",{staticStyle:{"margin-top":"15px"},attrs:{"label-width":"80px",inline:"",size:"mini"},nativeOn:{submit:function(t){t.preventDefault()}}},[a("el-date-picker",{attrs:{size:"mini",type:"daterange",format:"yyyy/MM/dd","range-separator":"至","start-placeholder":"开始日期","end-placeholder":"结束日期"},on:{change:t.getStatisticalList},model:{value:t.form.date,callback:function(e){t.$set(t.form,"date",e)},expression:"form.date"}}),t._v(" "),a("el-form-item",{attrs:{label:"部门"}},[a("el-select",{attrs:{placeholder:"请选择部门"},on:{change:t.getStatisticalList},model:{value:t.form.department_id,callback:function(e){t.$set(t.form,"department_id",e)},expression:"form.department_id"}},t._l(t.departmentList,function(t){return a("el-option",{key:t.department_id,attrs:{value:t.department_id,label:t.department_name}})}),1)],1),t._v(" "),a("el-form-item",{attrs:{label:"周期"}},[a("el-select",{attrs:{placeholder:"请选择站点"},on:{change:t.getStatisticalList},model:{value:t.form.cycle,callback:function(e){t.$set(t.form,"cycle",e)},expression:"form.cycle"}},[a("el-option",{attrs:{value:0,label:"日"}}),t._v(" "),a("el-option",{attrs:{value:1,label:"月"}}),t._v(" "),a("el-option",{attrs:{value:2,label:"年"}})],1)],1)],1)],1),t._v(" "),t._l(t.statisticalList,function(e){return a("el-row",{key:e.department_id,staticStyle:{border:"1px solid #e6e3e3"}},[a("el-col",{staticStyle:{padding:"0 15px"},attrs:{span:24}},[a("p",{staticStyle:{"font-size":"14px","font-weight":"bolder",padding:"0 15px"}},[a("span",{staticStyle:{"text-align":"left"}},[t._v(t._s(e.department_name))]),t._v(" "),a("span",{staticStyle:{"text-align":"right",cursor:"pointer"},on:{click:function(a){return t.openDetails(e)}}},[t._v("查看详情")])]),t._v(" "),a("el-row",t._l(e.quota_list,function(e){return a("el-col",{key:e.quota_id,staticStyle:{padding:"5px","text-align":"center"},attrs:{span:6}},[a("p",{staticStyle:{"font-weight":"bolder"}},[t._v(t._s(e.quota_name))]),t._v(" "),"0"!==e.total_complete_value||"0"!==e.total_target_value?a("el-progress",{attrs:{type:"circle","stroke-width":10,percentage:"0"}}):a("el-progress",{attrs:{type:"circle","stroke-width":10,percentage:e.total_complete_value/e.total_target_value}})],1)}),1)],1)],1)})],2),t._v(" "),a("el-dialog",{attrs:{title:t.title,visible:t.dialogVisible,width:"750px"},on:{"update:visible":function(e){t.dialogVisible=e}}},[a("div",{staticStyle:{width:"100%",height:"400px"},attrs:{id:"myChart"}}),t._v(" "),a("el-table",{staticStyle:{width:"100%"},attrs:{data:t.getDepartmentHead,border:""}},[a("el-table-column",{attrs:{type:"index"}}),t._v(" "),a("el-table-column",{attrs:{label:"姓名"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("span",[t._v(t._s(e.row.user_name||"--"))])]}}])}),t._v(" "),a("el-table-column",{attrs:{label:"指标名称"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("span",[t._v(t._s(e.row.quota_name||"--"))])]}}])}),t._v(" "),a("el-table-column",{attrs:{label:"时间"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("span",[t._v(t._s(t._f("dateFormat")(1e3*e.row.complete_date,"YYYY/MM/DD")))])]}}])}),t._v(" "),a("el-table-column",{attrs:{label:"录入量"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("span",[t._v(t._s(e.row.target_value))])]}}])}),t._v(" "),a("el-table-column",{attrs:{label:"完成度"},scopedSlots:t._u([{key:"default",fn:function(e){return[a("span",[t._v(t._s(e.row.complete_value/e.row.target_value))])]}}])})],1),t._v(" "),a("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{attrs:{size:"mini",type:"danger"},on:{click:t.cancle}},[t._v("取 消")]),t._v(" "),a("el-button",{attrs:{size:"mini",type:"primary"},on:{click:t.save}},[t._v("确 定")])],1)],1)],1)},staticRenderFns:[]};var r=a("VU/8")(l,n,!1,function(t){a("s4jm")},"data-v-5bfb53ed",null);e.default=r.exports},s4jm:function(t,e){}});
//# sourceMappingURL=21.b39e29fbcc8c44cde2fe.js.map