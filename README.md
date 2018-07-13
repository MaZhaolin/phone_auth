# 2018-07-13 v2.2.1

# 2018-07-11 v2.2.0
- 增加邮箱找回密码
- 适配审核选项，注册增加注册原因选项

# 2018-6-25 v2.1.0
- 增加自定义样式选项
- 绑定弹窗增加关闭按钮
- 用户增加解绑手机的功能

# 2018-6-13 v2.0.0
- 修复设置页，老用户无法绑定手机好的bug
- 修复设置页，输入框placeholder的编码问题

# 2018-5-28 v1.11.0
- 修复插件启用空白的问题
- 修复多站点用户跨站点激活的问题

# 2018-5-17 v1.10.0
- 修复gbk网站，用户名中文编码问题
- 修复部分网站无法正常解析200以外的状态码

# 2018-4-23 v1.9.3
- 修复移动端重定向过多的问题
- 修复移动端部分输入法输入中文用户名异常的问题
- 国际短信增加德国和英国区号选项
- 修复低版本启用空白

# 2018-3-28 v1.9.1
- 修复移动端部分浏览器粘贴不触发keyup事件，使用input代替
- 修复支付为创建的问题，延迟5s获取支付状态
- 修复原注册登录仍然可用的问题
- 修改设置样式

# 2018-3-27 v1.9.0
- 新增短信快速登录选项
- 修复gbk网站打开设置空白的问题
- 更新安装引导按钮的文字
- 后台设置兼容到ie9+

# 2018-3-22 v1.8.4
- 解决独立安装UCenter导致的登录注册出错的问题
- 后台设置用户管理中增加绑定手机好的功能
- 全局强制绑定手机号

# 2018-3-21 v1.8.3
- 后台均使用vue重构
- 修复充值问题

# 2018-3-20 v1.8.2
- 后台设置重构
- 修复编码问题

# 2018-3-15 v1.8.1
- 修复设置了安全问题导致的登录失败问题
- 修改导出数据的文件名
- 手机端注册增加邀请码
- 用户发发送验证码后可修改手机号
- 修复部分网站请求状态强制变更为`200`导致数据处理出错的问题
- 更新vaptcha sdk 32位机器`int`类型时间溢出的问题

# 2018-3-8 v1.8.0
- 兼容discuz邀请码注册
- 新增数据下载功能

# 2018-3-3 v1.7.0
- 新增第三方登录用户未绑定手机号的弹窗提示
- 限制未绑定手机号用户的发帖操作
- 修正qq登录绑定账号的页面

# 2018-2-23 v1.6.3
- 修复三方登录图标不显示的问题
- 修复低版本浏览器显示是验证按钮的问题
- 修正js中文编码问题

# 2018-2-23 v1.6.2
- 后台增加管理员解绑手机号
- 删除用户后，注册时自动删除空用户手机号

# 2018-2-11 v1.6.1
- 增加自定义qq和微信登陆地址
- 样式改为全局样式
- 解决gbk编码中文用户名登录的问题
- 解决gbk编码下部分网站无法支付的问题

# 2018-2-9 v1.6.0
- 增加qq和微信登录的显示，暂只支持discuz自带插件
- 修复升级时数据表未创建的问题
- 修复数据过期未更新问题
- 修复手机号已存在问题

# 2018-2-8 v1.5.0
- 新增安装时迁移原有的手机号数据
- 修改后台插件设置布局
- 新增用户列表查询
- 新增登录增场景号
