webpackJsonp([24],{"5CDu":function(e,r){},"d4T+":function(e,r,t){"use strict";Object.defineProperty(r,"__esModule",{value:!0});var s={render:function(){var e=this,r=e.$createElement,t=e._self._c||r;return t("div",{staticClass:"loginBox"},[t("div",{staticClass:"login"},[t("el-form",{ref:"form",staticStyle:{"margin-top":"50px"}},[t("el-form-item",{attrs:{label:"手机号：","label-width":"150px"}},[t("el-input",{attrs:{placeholder:"请输入手机号"},model:{value:e.form.mobile,callback:function(r){e.$set(e.form,"mobile",r)},expression:"form.mobile"}})],1),e._v(" "),t("el-form-item",{attrs:{label:"邮箱：","label-width":"150px"}},[t("el-input",{attrs:{placeholder:"请输入邮箱"},model:{value:e.form.email,callback:function(r){e.$set(e.form,"email",r)},expression:"form.email"}})],1),e._v(" "),t("el-form-item",{attrs:{label:"密码：","label-width":"150px"}},[t("el-input",{attrs:{type:"password",placeholder:"请输入密码"},model:{value:e.form.password,callback:function(r){e.$set(e.form,"password",r)},expression:"form.password"}})],1),e._v(" "),t("el-form-item",{attrs:{label:"确认密码：","label-width":"150px"}},[t("el-input",{attrs:{type:"password",placeholder:"请输入确认密码"},model:{value:e.rePassword,callback:function(r){e.rePassword=r},expression:"rePassword"}})],1),e._v(" "),t("el-form-item",{attrs:{label:"姓名：","label-width":"150px"}},[t("el-input",{attrs:{placeholder:"请输入姓名"},model:{value:e.form.name,callback:function(r){e.$set(e.form,"name",r)},expression:"form.name"}})],1),e._v(" "),t("el-form-item",{attrs:{label:"性别：","label-width":"150px"}},[t("el-radio-group",{model:{value:e.form.sex,callback:function(r){e.$set(e.form,"sex",r)},expression:"form.sex"}},[t("el-radio",{attrs:{label:"男"}}),e._v(" "),t("el-radio",{attrs:{label:"女"}})],1)],1),e._v(" "),t("el-form-item",{attrs:{label:"部门：","label-width":"150px"}},[t("el-input",{attrs:{placeholder:"请备注部门"},model:{value:e.form.note,callback:function(r){e.$set(e.form,"note",r)},expression:"form.note"}})],1)],1),e._v(" "),t("div",{staticStyle:{"text-align":"right","margin-right":"20%"}},[t("el-button",{attrs:{size:"mini",type:"primary"},on:{click:e.register}},[e._v("注册")])],1)],1)])},staticRenderFns:[]};var a=t("VU/8")({data:function(){return{showNav:!1,rePassword:"",form:{name:"",password:"",note:"",email:"",sex:"男",mobile:""}}},methods:{register:function(){var e=this;if(""!==this.form.mobile)if(""!==this.form.email)if(""!==this.form.password)if(""!==this.form.name){if(""===this.form.department_id&&this.$message.error("请选择部门"),this.rePassword!==this.form.password)return this.$message.error("两次密码不一致"),!1;"男"===this.form.sex?this.form.sex="1":this.form.sex="0",this.$post("/signUp",this.form).then(function(){e.$router.push({path:"/"})}).catch(function(r){e.$message.error(r)})}else this.$message.error("用户名不能为空");else this.$message.error("密码不能为空");else this.$message.error("邮箱不能为空");else this.$message.error("手机号不能为空")}}},s,!1,function(e){t("5CDu")},"data-v-52922aaf",null);r.default=a.exports}});
//# sourceMappingURL=24.f1d70aa535683bec50d4.js.map