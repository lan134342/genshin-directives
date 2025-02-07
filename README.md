# genshin-directives
  原神真端的muip的web在线执行指令
 
 先简单提一嘴：作者写的第一个开源项目，配合了GPT4一起写的，之前没什么开发经验，也算是我的第好几个小项目，不过是第一次开源代码~写的不好请提出来哦！
 
 项目介绍（简单）：本项目是通过原神真端的MUip的一定文档，采取HTTP请求头对MUip进行请求，然后可以实现指令的功能
 
 需要部署教程可以滑倒最后
 
 ### 1.项目结构


```
目录结构
├── 根目录
│   ├── .htaccess (服务器配置)
│   ├── .user.ini (用户配置)
│   ├── 404.html (404页面)
│   ├── backend.php (后端脚本)
│   ├── check-login.php (登录检查)
│   ├── data/ (数据文件夹，储存注册数据)
│   ├── db.sqlite3 (数据库文件)
│   ├── execution_log.txt (执行日志)
│   ├── index.html (首页)
│   ├── login.html (登录页面)
│   ├── login.php (登录逻辑)
│   ├── records/ (记录文件夹)
│   │   ├── command_logs.txt (命令日志)
│   │   ├── ip_logs.txt (IP日志)
│   │   └── zl.txt
│   ├── register.html (注册页面)
│   └── register.php (注册逻辑)
```

关于上面就是本项目的结构了！

大家应该都能看得出来，整个网站是采用了php+HTML代码，前后端混合（为什么不是分离，是因为懒）

### 2.项目介绍
刚刚简单提了一嘴，本项目说白了就是请求MUip，所以后端必须开启MUip（GM），否则不能正常使用！

这个项目目前只支持真端（官方服务端），暂不支持割草机，看看以后我有没有需求再去做割草机的，这个项目我纯粹是闲着没事干写着好玩的，bug多请不要介意哈！

#### 有实力的大佬可以二开或者修改一下，只要给我点个star~

接下来是对项目的具体解释：

backend.php

这个是核心的php配置，所有的内容基本上是围着这个转的，看一下下面的图片

![image](https://github.com/user-attachments/assets/1688ae9c-63cf-4b64-8a5f-bdfd773d2bbc)

这里就是携带请求头，理论上来说，MUip的请求应该都是：

http://127.0.0.1:23406/api?region=sans_dev&ticket=GM&cmd=1116&uid
=1&msg=item add 223 1

这里127.0.0.1要换，部分用户23406要换（我这里是动漫研究社的端），后面的uid=1要换，msg=item add 223 1也要换

所以我就在后端写了个变量![image](https://github.com/user-attachments/assets/7e2859de-5baa-4c00-9562-5a7194eb4f65)

利用这边变量去调用了。

login和register两个php、HTML就是注册登录，注册登陆的数据都在data，如：注册的uid是1，那么data里面的文件就是1.json

然后还有一个check-login.php就是检测是否登录的逻辑了，比较简单

其他的就没什么好说的了

## 部署教程

宝塔添加个站点，php版本高一点都行

进入 backend.php中 修改一下以下的内容

进入 backend.php中 修改一下

![image](https://github.com/user-attachments/assets/ea1d7de1-936d-4e1c-954b-0dacbd689d32)

第一个MUip必须带/api 比如http://127.0.0.1:23406/api

这个样子，否则不能正常执行指令

第二个是区服，一般端里面都有告诉你区服

然后就没什么要改的了

![image](https://github.com/user-attachments/assets/efe3a620-5c6d-443f-b7d7-f76fa2cea9ce)

如有要的话，title标签里面的标题也可以改改

还有一个

#### 有一个zl.txt文件，records目录里面，里面是允许执行指令的uid

一行一个

比如我想让1、2、3uid可以执行指令，其他的不行，那么就：

```
1
2
3
```

就是这样的，如果相反，想改成123不能执行指令，就改一下backend.php中的判断逻辑，配合GPT挺好改的

![image](https://github.com/user-attachments/assets/6cbca927-9553-4401-b97e-f0bc7b42c486)

好像主要就是这个的事情，把false改成ture应该就好了（不确定，可以自己试试哦）

前端的背景我都是用的https://www.dmoe.cc/random.php，可以自己改改

二开请标注一下原作者、点个star就好了，最后，玩的开心！
