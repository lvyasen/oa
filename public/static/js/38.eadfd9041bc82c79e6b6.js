webpackJsonp([38],{PjLA:function(t,e){},rAcn:function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var n={components:{NavTemplent:a("15QY").a},name:"index",methods:{getInitialData:function(){gapi.analytics.ready(function(){gapi.analytics.auth.authorize({container:"auth-button",clientid:"628874914398-uqthhdqsb0sv06s1sork5j1uf4k69555.apps.googleusercontent.com"});var t=new gapi.analytics.ViewSelector({container:"view-selector"}),e=new gapi.analytics.googleCharts.DataChart({reportType:"ga",query:{dimensions:"ga:source",metrics:"ga:organicSearches","start-date":"30daysAgo","end-date":"yesterday"},chart:{type:"PIE",container:"timeline"}}),a=new gapi.analytics.googleCharts.DataChart({reportType:"ga",query:{dimensions:"ga:fullReferrer",metrics:"ga:organicSearches","start-date":"30daysAgo","end-date":"yesterday"},chart:{type:"LINE",container:"timelines"}});t.on("change",function(t){var e={query:{ids:t}};a.set(e).execute()}),gapi.analytics.auth.on("success",function(e){t.execute()}),t.on("change",function(t){var a={query:{ids:t}};e.set(a).execute()})})}},mounted:function(){this.getInitialData()}},i={render:function(){var t=this.$createElement,e=this._self._c||t;return e("div",[e("NavTemplent",{attrs:{title:"人群分析"}}),this._v(" "),e("div",{staticStyle:{padding:"15px 10% 15px 15px"}},[e("section",{attrs:{id:"auth-button"}}),this._v(" "),e("section",{attrs:{id:"view-selector"}}),this._v(" "),e("el-row",[e("el-col",{attrs:{span:12}},[e("section",{attrs:{id:"timeline"}})]),this._v(" "),e("el-col",{attrs:{span:12}},[e("section",{attrs:{id:"timelines"}})])],1)],1)],1)},staticRenderFns:[]};var s=a("VU/8")(n,i,!1,function(t){a("PjLA")},"data-v-26013bf5",null);e.default=s.exports}});
//# sourceMappingURL=38.eadfd9041bc82c79e6b6.js.map