webpackJsonp([39],{VcBb:function(e,t){},fOZK:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var i=a("ryJa"),s=a.n(i),r=a("15QY"),l=a("8dnD"),o={components:{NavTemplent:r.a,TablePage:l.a},data:function(){return{userQuotaList:[],quotaList:[],editUserMessage:null,departmentList:[],page:{pageSize:1,pageNum:50},editUse:null,title:"人事部门指标修改",isDepartent:!1,dialogVisible:!1,userList:[],total:0,userId:"",form:{finishDegree:"",userName:"",complete_date:"",date:[s()(s()().valueOf()-6048e5).format("YYYY/MM/DD"),s()(s()()+864e5).format("YYYY/MM/DD")],departmentId:"",user_id:"",quota_id:"",target_value:"",unit:"",target_date_type:"",complete_value:"",score:"",remark:""}}},methods:{getDepartmentList:function(){var e=this;this.$post("/getDepartmentList").then(function(t){e.departmentList=t.list.filter(function(e){return"人事部"===e.department_name}),e.$post("getQuotaList",{pageNum:e.page.pageNum,page:e.page.pageSize,department_id:e.departmentList[0].department_id}).then(function(t){e.quotaList=t.list}),e.isDepartent?(e.startTime=e.$root.$options.filters.dateFormat(e.form.date[0],"YYYY/MM/DD"),e.lastTime=e.$root.$options.filters.dateFormat(e.form.date[1],"YYYY/MM/DD"),e.$post("getUserQuotaList",{pageNum:e.page.pageNum,page:e.page.pageSize,department_id:e.departmentList[0].department_id,user_id:e.userId,start_time:e.startTime,end_time:e.lastTime}).then(function(t){e.userQuotaList=t.list})):e.$post("/getUserList",{pageNum:e.page.pageNum,page:e.page.pageSize,department_id:e.departmentList[0].department_id}).then(function(t){e.total=t.count,e.userList=t.list})}).catch(function(t){e.$message.error(t)})},updateSize:function(e){this.page.pageSize=1,this.page.pageNum=e,this.getDepartmentList()},updateNum:function(e){this.page.pageSize=e,this.getDepartmentList()},openDetails:function(e){var t=this;this.userId=e.id,this.isDepartent=!0,this.startTime=this.$root.$options.filters.dateFormat(this.form.date[0],"YYYY/MM/DD"),this.lastTime=this.$root.$options.filters.dateFormat(this.form.date[1],"YYYY/MM/DD"),this.$post("getUserQuotaList",{pageNum:this.page.pageNum,page:this.page.pageSize,department_id:this.departmentList[0].department_id,user_id:e.id,start_time:this.startTime,end_time:this.lastTime}).then(function(e){t.userQuotaList=e.list})},editquatoMessage:function(e){this.editUserMessage=e,this.dialogVisible=!0,e.user_quota_id?(this.form=e,this.form.complete_date=1e3*e.complete_date):(this.form.user_id="",this.form.quota_id="",this.form.target_value="",this.form.unit="",this.form.target_date_type="",this.form.complete_value="",this.form.score="",this.form.complete_date="",this.form.remark="")},delquatoMessage:function(e){var t=this;this.$confirm("此操作将永久删除该文件, 是否继续?","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){t.$post("delUserQuota",{user_quota_id:e.user_quota_id}).then(function(){t.getDepartmentList(),t.$message.success("指标删除成功")})}).catch(function(){t.$message({type:"info",message:"已取消删除"})})},cancle:function(){this.dialogVisible=!1,this.$emit("close")},save:function(){var e=this;this.editUserMessage.user_quota_id?this.$post("editUserQuota",{user_quota_id:this.editUserMessage.user_quota_id,user_id:this.form.user_id,department_id:this.departmentList[0].department_id,target_value:this.form.target_value,target_date_type:this.form.target_date_type,complete_value:this.form.complete_value,score:this.form.score,complete_date:s()(this.form.complete_date).format("YYYY/MM/DD"),remark:this.form.remark,unit:this.form.unit,quota_id:this.form.quota_id}).then(function(t){e.$message.success("用户指标修改成功"),e.dialogVisible=!1,e.$emit("close"),e.getDepartmentList()}):this.$post("addUserQuota",{user_id:this.form.user_id,department_id:this.departmentList[0].department_id,target_value:this.form.target_value,target_date_type:this.form.target_date_type,complete_value:this.form.complete_value,score:this.form.score,complete_date:this.form.complete_date,remark:this.form.remark,unit:this.form.unit,quota_id:this.form.quota_id}).then(function(t){e.$message.success("用户新增成功"),e.dialogVisible=!1,e.$emit("close"),e.getDepartmentList()})}},mounted:function(){this.getDepartmentList()}},n={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",[a("NavTemplent",{attrs:{title:"人事部门指标项"}}),e._v(" "),a("div",{staticStyle:{padding:"15px 10% 15px 15px"}},[a("div",[a("h5",{staticStyle:{"font-weight":"bold"}},[e._v("数据筛选")]),e._v(" "),a("el-form",{staticStyle:{"margin-top":"15px"},attrs:{"label-width":"80px",inline:"",size:"mini"},nativeOn:{submit:function(e){e.preventDefault()}}},[a("el-date-picker",{staticStyle:{width:"250px"},attrs:{size:"mini",type:"daterange",format:"yyyy/MM/dd","start-placeholder":"开始日期","end-placeholder":"结束日期"},on:{change:e.getDepartmentList},model:{value:e.form.date,callback:function(t){e.$set(e.form,"date",t)},expression:"form.date"}}),e._v(" "),e.isDepartent?e._e():a("el-form-item",{attrs:{label:"姓名"}},[a("el-input",{attrs:{placeholder:"请输入姓名"},nativeOn:{keyup:function(t){return!t.type.indexOf("key")&&e._k(t.keyCode,"enter",13,t.key,"Enter")?null:e.getDepartmentList(t)}},model:{value:e.form.userName,callback:function(t){e.$set(e.form,"userName",t)},expression:"form.userName"}})],1)],1)],1),e._v(" "),e.isDepartent?e._e():a("div",[e._m(0),e._v(" "),a("el-table",{staticStyle:{width:"100%"},attrs:{data:e.userList,border:""}},[a("el-table-column",{attrs:{type:"index"}}),e._v(" "),a("el-table-column",{attrs:{label:"员工姓名"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-link",{staticStyle:{color:"#409EFF"},on:{click:function(a){return e.openDetails(t.row)}}},[e._v(e._s(t.row.name||"--"))])]}}],null,!1,2935767415)}),e._v(" "),e._l(e.quotaList,function(t){return a("el-table-column",{key:t.quota_id,attrs:{label:t.quota_name},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-link",[e._v("--")])]}}],null,!0)})})],2),e._v(" "),a("TablePage",{attrs:{pageSize:e.page.pageNum,pageNum:e.page.pageSize,total:e.total},on:{"update:pageSize":e.updateSize,"update:pageNum":e.updateNum}})],1),e._v(" "),e.isDepartent?a("div",[a("div",{staticClass:"tableTitle"},[e._m(1),e._v(" "),a("span",{staticStyle:{width:"50%",float:"right","text-align":"right"}},[a("el-button",{attrs:{size:"mini",type:"primary"},on:{click:e.editquatoMessage}},[a("i",{staticClass:"el-icon-plus"}),e._v("添加")])],1)]),e._v(" "),a("el-table",{staticStyle:{width:"100%"},attrs:{data:e.userQuotaList,border:""}},[a("el-table-column",{attrs:{type:"index"}}),e._v(" "),a("el-table-column",{attrs:{label:"指标名称"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-link",[e._v(e._s(t.row.quota_name||"--"))])]}}],null,!1,1950969544)}),e._v(" "),a("el-table-column",{attrs:{label:"目标值"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("span",[e._v(e._s(t.row.target_value||"--"))])]}}],null,!1,2652049859)}),e._v(" "),a("el-table-column",{attrs:{label:"实际完成值"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("span",[e._v(e._s(t.row.complete_value||"--"))])]}}],null,!1,2570400379)}),e._v(" "),a("el-table-column",{attrs:{label:"得分项"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("span",[e._v(e._s(t.row.score||"--"))])]}}],null,!1,3697977646)}),e._v(" "),a("el-table-column",{attrs:{label:"时间"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("span",[e._v(e._s(e._f("dateFormat")(t.row.add_time,"YYYY/MM/DD")))])]}}],null,!1,3122892559)}),e._v(" "),a("el-table-column",{attrs:{label:"操作"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-link",{staticStyle:{color:"#409EFF"},on:{click:function(a){return e.editquatoMessage(t.row)}}},[e._v("编辑")]),e._v(" "),a("el-link",{staticStyle:{color:"#409EFF"},on:{click:function(a){return e.delquatoMessage(t.row)}}},[e._v("删除")])]}}],null,!1,3193845282)})],1)],1):e._e(),e._v(" "),a("el-dialog",{attrs:{title:e.title,visible:e.dialogVisible,width:"750px","append-to-body":""},on:{"update:visible":function(t){e.dialogVisible=t}}},[a("el-form",{attrs:{size:"mini"}},[a("el-form-item",{attrs:{label:"姓名"}},[a("el-select",{attrs:{placeholder:"请选择姓名"},model:{value:e.form.user_id,callback:function(t){e.$set(e.form,"user_id",t)},expression:"form.user_id"}},e._l(e.userList,function(e){return a("el-option",{key:e.id,attrs:{label:e.name,value:e.id}})}),1)],1),e._v(" "),a("el-form-item",{attrs:{label:"指标"}},[a("el-select",{attrs:{placeholder:"请选择指标"},model:{value:e.form.quota_id,callback:function(t){e.$set(e.form,"quota_id",t)},expression:"form.quota_id"}},e._l(e.quotaList,function(e){return a("el-option",{key:e.quota_id,attrs:{label:e.quota_name,value:e.quota_id}})}),1)],1),e._v(" "),a("el-form-item",{attrs:{label:"目标值"}},[a("el-input",{attrs:{placeholder:"请输入目标值"},model:{value:e.form.target_value,callback:function(t){e.$set(e.form,"target_value",t)},expression:"form.target_value"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"单位"}},[a("el-select",{attrs:{placeholder:"请选择单位"},model:{value:e.form.unit,callback:function(t){e.$set(e.form,"unit",t)},expression:"form.unit"}},[a("el-option",{attrs:{label:"百分比",value:1}}),e._v(" "),a("el-option",{attrs:{label:"个数",value:0}})],1)],1),e._v(" "),a("el-form-item",{attrs:{label:"目标期类型"}},[a("el-select",{attrs:{placeholder:"请选择目标类型"},model:{value:e.form.target_date_type,callback:function(t){e.$set(e.form,"target_date_type",t)},expression:"form.target_date_type"}},[a("el-option",{attrs:{label:"日",value:1}}),e._v(" "),a("el-option",{attrs:{label:"周",value:2}}),e._v(" "),a("el-option",{attrs:{label:"月",value:3}})],1)],1),e._v(" "),a("el-form-item",{attrs:{label:"实际完成值"}},[a("el-input",{attrs:{placeholder:"请输入实际完成值"},model:{value:e.form.complete_value,callback:function(t){e.$set(e.form,"complete_value",t)},expression:"form.complete_value"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"该指标完成度"}},[a("el-input",{attrs:{placeholder:"请输入该指标完成度"},model:{value:e.form.finishDegree,callback:function(t){e.$set(e.form,"finishDegree",t)},expression:"form.finishDegree"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"该项得分"}},[a("el-input",{attrs:{placeholder:"请输入得分"},model:{value:e.form.score,callback:function(t){e.$set(e.form,"score",t)},expression:"form.score"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"选择日期"}},[a("el-date-picker",{attrs:{type:"date",placeholder:"选择日期"},model:{value:e.form.complete_date,callback:function(t){e.$set(e.form,"complete_date",t)},expression:"form.complete_date"}})],1),e._v(" "),a("el-form-item",{attrs:{label:"备注"}},[a("el-input",{attrs:{placeholder:"请输入备注"},model:{value:e.form.remark,callback:function(t){e.$set(e.form,"remark",t)},expression:"form.remark"}})],1)],1),e._v(" "),a("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{attrs:{size:"mini",type:"danger"},on:{click:e.cancle}},[e._v("取 消")]),e._v(" "),a("el-button",{attrs:{size:"mini",type:"primary"},on:{click:e.save}},[e._v("确 定")])],1)],1)],1)],1)},staticRenderFns:[function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"tableTitle"},[t("span",[t("div",[this._v("指标列表")])])])},function(){var e=this.$createElement,t=this._self._c||e;return t("span",{staticStyle:{width:"50%",float:"left"}},[t("div",[this._v("数据列表")])])}]};var u=a("VU/8")(o,n,!1,function(e){a("VcBb")},"data-v-214095b9",null);t.default=u.exports}});
//# sourceMappingURL=39.0fa878d6b674c04b60ea.js.map