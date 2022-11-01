import * as storage from './storage.js' //引入common
let payTypelist = ["缴纳订单", "缴纳押金"]; //支付类型
let payStatelist=["未支付","已支付"];//支付状态
let orderStatelist = [{
	name: "进行中",
	value: "ING"
}, {
	name: "已完成",
	value: "FINISHED"
}, {
	name: "弹出失败",
	value: "EJECTFAILED"
}, {
	name: "归还失败",
	value: "RETURNFAILED"
}]; //订单类型

let payWaystringlist = [{
	name: "微信支付宝",
	value: "WECHAT"
}, {
	name: "支付宝",
	value: "ALIPAY"
}, {
	name: "信用宝",
	value: "CREDITSCORE"
}]; //订单类型

// 服务类型
let serviceTypeList = [
	{
		name:['回输'],
		value:'1'
	},
	{
		name:['存储'],
		value:'2'
	},
	{
		name:'3',
		value:['回输','存储']
	},
	{
		name:'4',
		value:['其他']
	}
]
let statusTypeList = [
	{
		name:'已核销',
		value:'1'
	},
	{
		name:'待核销',
		value:'0'
	},
]

let navtop = 0; //导航距离上面的位置
let customBar = 0; //导航栏的高度
let classipad; //是否是平板布局
//把obj对象里的值覆盖到newobj里面
function deepCopy(newobj, obj) {
	if (typeof obj != 'object') {
		return obj
	}
	for (var attr in obj) {
		var a = {}
		if (newobj[attr]) {
			a = newobj[attr]
		}
		newobj[attr] = deepCopy(a, obj[attr])
	}
	return newobj
}

//跳转到登陆页面
function jumpToLogin() {
	var now_time = Date.parse(new Date())
	var value = storage.get('jump_to_login')
	if (!value) {
		value = 0
	}
	if (now_time - value > 3000) {
		storage.set('jump_to_login', now_time); //暂时屏蔽登录时间查询
		// 将当前页面route存vuex中 登录注册后跳转
		let pages = getCurrentPages()
		let page = pages[pages.length - 1]
		// 获取页面参数信息
		let pagePath = ''
		pagePath = '/pages/index/index'
		if (pagePath) {

		}
		console.log("请先登录!0")
		uni.showToast({
			title: '请先登录!',
			icon: 'none',
			duration: 1000,
			success: function(res) {
				// #ifdef MP-WEIXIN || MP-ALIPAY
				setTimeout(() => {
					uni.hideToast();
					uni.navigateTo({
						url: '/pages/login/auth/auth',
						animationType: 'pop-in',
						animationDuration: 200
					})
				}, 500)
				// #endif
			}
		})
	}
}

//当出错的时候，显示错误信息，并且跳转 弃用
/* function errorToBack(msg = '出错了，请重试',delta=1){
  uni.showToast({
    title: msg,
    icon: 'none',
    duration: 2000,
  });
  if(delta > 0){
    setTimeout(function () {
      uni.navigateBack({
        delta: delta
      })
    }, 1000);
  }
} */
//操作成功后，的提示信息
function successToShow(msg = '保存成功', callback = function() {}) {
	wx.showLoading();
	wx.hideLoading();
	setTimeout(() => {
		wx.showToast({
			title: msg,
			icon: "success",
		});
		setTimeout(() => {
			wx.hideToast();
		}, 2000)
	}, 0);
	// setTimeout(function() {
	//   callback()
	// }, 1500)
}

//操作失败的提示信息
function errorToShow(msg = '操作失败', callback = function() {}) {

	wx.showLoading();
	wx.hideLoading();
	setTimeout(() => {
		wx.showToast({
			title: msg,
			icon: "none",
		});
		setTimeout(() => {
			wx.hideToast();
		}, 2000)
	}, 0);
}

//加载显示
function loadToShow(msg = '加载中') {
	uni.showToast({
		title: msg,
		icon: 'loading'
	})
}

//加载隐藏
function loadToHide() {
	uni.hideToast()
}

// 提示框
function modelShow(
	title = '提示',
	content = '确认执行此操作吗?',
	callback = () => {},
	showCancel = true,
	cancelText = '取消',
	confirmText = '确定',
	cancelColor = '#000000',
	confirmColor = '#576B95',
	cancleCallBack = () => {}
) {
	console.log(callback)
	uni.showModal({
		title: title,
		content: content,
		showCancel: showCancel,
		cancelText: cancelText,
		confirmText: confirmText,
		cancelText: cancelText,
		cancelColor:cancelColor,
		confirmColor:confirmColor,
		success: function(res) {
			if (res.confirm) {
				// 用户点击确定操作
				setTimeout(() => {
					callback()
				}, 500)
			} else if (res.cancel) {
				// 用户取消操作
				cancleCallBack()
			}
		}
	})
}
// 获取当前年月
const currentMonth = function() {
	let date = new Date()
	let year = date.getFullYear();
	let month = date.getMonth() + 1 < 10 ? "0" + (date.getMonth() + 1)
	 : date.getMonth() + 1;
	let dateStr = `${year}年${month}月`;
	return dateStr
}
// 获取当前时间
const currentTime = function() {
	// let date = new Date()
	// let dateStr =
	// 	`${date.getFullYear()}-${date.getMonth()+1}-${date.getDate()} ${date.getHours()}:${date.getMinutes()}`
	// return dateStr
	let date = new Date()
	let year = date.getFullYear();
	let month = date.getMonth() + 1 < 10 ? "0" + (date.getMonth() + 1)
	 : date.getMonth() + 1;
	let day = date.getDate() < 10 ? "0" + date.getDate() : date
	 .getDate();
	// let hour = date.getHours() < 10 ? "0" + date.getHours() : date.getHours()
	// let minute = date.getMinutes() < 10 ? "0" + date.getMinutes() : date.getMinutes()
	let dateStr = `${year}-${month}-${day}`;
	return dateStr
}

// 格式化时间字符串
// {{formatDatetime(item.startTime,'yyyy-MM-dd hh:mm')}}
function formatDatetime(datetime, fmt = 'yyyy-MM-dd hh:mm:ss') {
	console.log(datetime)
	// if (String(datetime).indexOf("-") > -1) {
	datetime = String(datetime).replace('T', ' ').replace('.000+0800', '').replace('.000+08:00', '').replace(/-/g, '/')
	// }
	console.log("去掉杠的时间", datetime)

	let date = new Date(datetime)
	console.log("格式化时间", date)
	if (/(y+)/.test(fmt)) {
		fmt = fmt.replace(RegExp.$1, (date.getFullYear() + '').substr(4 - RegExp.$1.length));
	}
	let o = {
		'M+': date.getMonth() + 1,
		'd+': date.getDate(),
		'h+': date.getHours(),
		'm+': date.getMinutes(),
		's+': date.getSeconds()
	};
	for (let k in o) {
		if (new RegExp(`(${k})`).test(fmt)) {
			let str = o[k] + '';
			fmt = fmt.replace(RegExp.$1, (RegExp.$1.length === 1) ? str : ('00' + str).substr(str.length));
		}
	}
	return fmt;
}
// 时间计算
function getDate(date, AddMonthCount = 0, AddDayCount = 0) {
	if (typeof date !== 'object') {
		date = date.replace('T', ' ').replace('.000+0800', '').replace(/-/g, '/')
	}
	let dd = new Date(date)
	dd.setMonth(dd.getMonth() + AddMonthCount) // 获取AddDayCount天后的日期
	dd.setDate(dd.getDate() + AddDayCount) // 获取AddDayCount天后的日期
	let y = dd.getFullYear()
	let m = dd.getMonth() + 1 < 10 ? '0' + (dd.getMonth() + 1) : dd.getMonth() + 1 // 获取当前月份的日期，不足10补0
	let d = dd.getDate() < 10 ? '0' + dd.getDate() : dd.getDate() // 获取当前几号，不足10补0
	return y + '-' + m + '-' + d
}
//时间戳转时间格式
function timeToDate(date, flag = false) {
	var date = new Date(date * 1000) //如果date为13位不需要乘1000
	var Y = date.getFullYear() + '-'
	var M =
		(date.getMonth() + 1 < 10 ?
			'0' + (date.getMonth() + 1) :
			date.getMonth() + 1) + '-'
	var D = (date.getDate() < 10 ? '0' + date.getDate() : date.getDate()) + ' '
	var h = (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':'
	var m =
		(date.getMinutes() < 10 ? '0' + date.getMinutes() : date.getMinutes()) + ':'
	var s = date.getSeconds() < 10 ? '0' + date.getSeconds() : date.getSeconds()
	if (flag) {
		return Y + M + D
	} else {
		return Y + M + D + h + m + s
	}
}

function time2date(micro_second) { //秒数转时分秒
	var time = {}
	// 总秒数
	var second = Math.floor(micro_second)
	// 天数
	time.day = PrefixInteger(Math.floor(second / 3600 / 24), 2)
	// 小时
	time.hour = PrefixInteger(Math.floor((second / 3600) % 24), 2)
	// 分钟
	time.minute = PrefixInteger(Math.floor((second / 60) % 60), 2)
	// 秒
	time.second = PrefixInteger(Math.floor(second % 60), 2)

	var newtime = ''
	if (time.day > 0) {
		newtime =
			time.day +
			'天' +
			time.hour +
			'小时' +
			time.minute +
			'分' +
			time.second +
			'秒'
	} else {
		if (time.hour != 0) {
			newtime = time.hour + '小时' + time.minute + '分' + time.second + '秒'
		} else {
			newtime = time.minute + '分' + time.second + '秒'
		}
	}
	return newtime
}

function timeToDateObj(micro_second) {
	var time = {}
	// 总秒数
	var second = Math.floor(micro_second)
	// 天数
	time.day = Math.floor(second / 3600 / 24)
	// 小时
	time.hour = Math.floor((second / 3600) % 24)
	// 分钟
	time.minute = Math.floor((second / 60) % 60)
	// 秒
	time.second = Math.floor(second % 60)

	return time

}

// 时间转毫秒
// '2018-07-09 14:13:11'
function getTime(datetime) {
	var startDate = datetime;
	startDate = startDate.replace(new RegExp("-", "gm"), "/");
	var startDateM = (new Date(startDate)).getTime(); //得到毫秒数
	return startDateM
}

//货币格式化
function formatMoney(number, places, symbol, thousand, decimal) {
	number = number || 0
	places = !isNaN((places = Math.abs(places))) ? places : 2
	symbol = symbol !== undefined ? symbol : '￥'
	thousand = thousand || ','
	decimal = decimal || '.'
	var negative = number < 0 ? '-' : '',
		i = parseInt((number = Math.abs(+number || 0).toFixed(places)), 10) + '',
		j = (j = i.length) > 3 ? j % 3 : 0
	return (
		symbol +
		negative +
		(j ? i.substr(0, j) + thousand : '') +
		i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + thousand) +
		(places ?
			decimal +
			Math.abs(number - i)
			.toFixed(places)
			.slice(2) :
			'')
	)
}

function throttle(fn, context, delay) {
	clearTimeout(fn.timeoutId)
	fn.timeoutId = setTimeout(function() {
		fn.call(context)
	}, delay)
}

// 时间格式化输出，如11:03 25:19 每1s都会调用一次
function dateformat(micro_second) {
	var time = {}
	// 总秒数
	var second = Math.floor(micro_second / 1000) // 天数
	time.day = PrefixInteger(Math.floor(second / 3600 / 24), 2) // 小时
	time.hour = PrefixInteger(Math.floor((second / 3600) % 24), 2) // 分钟
	time.minute = PrefixInteger(Math.floor((second / 60) % 60), 2) // 秒
	time.second = PrefixInteger(Math.floor(second % 60), 2)
	return time
}

//不足位数前面补0
function PrefixInteger(num, length) {
	return (Array(length).join('0') + num).slice(-length)
}

//验证是否是手机号
function isPhoneNumber(str) {
	var myreg = /^[1][3,4,5,6,7,8,9][0-9]{9}$/
	if (!myreg.test(str)) {
		return false
	} else {
		return true
	}
}

function timeformat(StatusMinute){

 var day = parseInt(StatusMinute / 60 / 24);
    var hour = parseInt(StatusMinute / 60 % 24);
    var min = parseInt(StatusMinute % 60);
    StatusMinute = "";
    if (day > 0) {
        StatusMinute = day + "天";
    }
    if (hour > 0) {
        StatusMinute += hour + "小时";
    }
    if (min > 0) {
        StatusMinute += parseFloat(min) + "分钟";
    }
    //三元运算符 传入的分钟数不够一分钟 默认为0分钟，else return 运算后的StatusMinute 
    return StatusMinute == "" ? "0分钟": StatusMinute;
}

/**
 *
 * 对象参数转为url参数
 *
 */
function builderUrlParams(url, data) {
	if (typeof url == 'undefined' || url == null || url == '') {
		return ''
	}
	if (typeof data == 'undefined' || data == null || typeof data != 'object') {
		return ''
	}
	url += url.indexOf('?') != -1 ? '' : '?'
	for (var k in data) {
		url += (url.indexOf('=') != -1 ? '&' : '') + k + '=' + encodeURI(data[k])
	}
	return url
}

/**
 * 使用循环的方式判断一个元素是否存在于一个数组中
 * @param {Object} arr 数组
 * @param {Object} value 元素值
 */
function isInArray(arr, value) {
	for (var i = 0; i < arr.length; i++) {
		if (value === arr[i]) {
			return true
		}
	}
	return false
}
/**
 * 统一跳转
 */
function navigateTo(url) {
	uni.navigateTo({
		url: url,
		animationType: 'pop-in',
		animationDuration: 300
	})
}

/**
 *  关闭当前页面并跳转
 */
function redirectTo(url) {
	uni.redirectTo({
		url: url,
		animationType: 'pop-in',
		animationDuration: 300
	})
}

function switchTab(url) {
	uni.switchTab({
		url: url,
		animationType: 'pop-in',
		animationDuration: 300
	})
}

function reLaunch(url) {
	uni.reLaunch({
		url: url,
		animationType: 'pop-in',
		animationDuration: 300
	})
}

function navigateBack(index) {
	uni.navigateBack({
		delta: index
	});
}


function setNavigationBarTitle(title) {
	uni.setNavigationBarTitle({
		title: title
	});
}
/**
 * 获取url参数
 *
 * @param {*} name
 * @param {*} [url=window.location.serach]
 * @returns
 */
function getQueryString(name, url) {
	console.log(name, url)
	var url = url || window.location.href
	var reg = new RegExp('(^|&|/?)' + name + '=([^&|/?]*)(&|/?|$)', 'i')
	var r = url.substr(1).match(reg)
	if (r != null) {
		return r[2]
	}
	return null
}

/**
 *
 *  判断是否在微信浏览器 true是
 */
function isWeiXinBrowser() {
	// #ifdef H5
	// window.navigator.userAgent属性包含了浏览器类型、版本、操作系统类型、浏览器引擎类型等信息，这个属性可以用来判断浏览器类型
	let ua = window.navigator.userAgent.toLowerCase()
	// 通过正则表达式匹配ua中是否含有MicroMessenger字符串
	if (ua.match(/MicroMessenger/i) == 'micromessenger') {
		return true
	} else {
		return false
	}
	// #endif

	return false
}

/**
 * 金额相加
 * @param {Object} value1
 * @param {Object} value2
 */
function moneySum(value1, value2) {
	return (parseFloat(value1) + parseFloat(value2)).toFixed(2);
}
/**
 * 金额相减
 * @param {Object} value1
 * @param {Object} value2
 */
function moneySub(value1, value2) {
	let res = (parseFloat(value1) - parseFloat(value2)).toFixed(2);
	return res > 0 ? res : 0;
}


/**
 * 分享URL解压缩
 * @param {Object} url
 */
function shareParameterEncode(url) {
	let urlArray = url.split('-');
	let newUrl = 'type=' + urlArray[0] + '&invite=' + urlArray[1] + '&id=' + urlArray[2] + '&team_id=' + urlArray[3] +
		'&id_type=' + urlArray[4] + '&page_code=' + urlArray[5] + '&group_id=' + urlArray[6];
	return newUrl;
}


/**
 * 分享URL压缩
 * @param {Object} url
 */
function shareParameterDecode(url) {
	var urlArray = url.split('&');
	var allParameter = {
		'type': '',
		'invite': '',
		'id': '',
		'team_id': '',
		'id_type': '',
		'page_code': '',
		'group_id': '',
	};
	for (var i = 0; i < urlArray.length; i++) {
		var parameter = urlArray[i].split('=');
		allParameter[parameter[0]] = parameter[1];
	}
	var newUrl = allParameter.type + '-' + allParameter.invite + '-' + allParameter.id + '-' + allParameter.team_id +
		'-' +
		allParameter.id_type + '-' + allParameter.page_code + '-' + allParameter.group_id;
	return newUrl;
}

function SourceHanSansCN() {
	//引入外部字体
	uni.loadFontFace({
		family: 'SourceHanSansCN',
		source: 'url("https://images.xmojiang.com/SourceHanSansCN%20Medium.ttf")',
		success() {
			console.log("思源黑字体加载成功")
		}
	})
}

function SourceHanSerifCN() {
	//引入外部字体
	uni.loadFontFace({
		family: 'SourceHanSerifCN',
		source: 'url("https://images.xmojiang.com/SourceHanSerifCN-SemiBold.otf")',
		success() {
			console.log("黑雅字体加载成功")
		}
	})
}

function stsongtiscblack() {
	//引入外部字体
	uni.loadFontFace({
		family: 'Songti-SC-Black',
		source: 'url("https://images.xmojiang.com/Songti-SC-Black02.ttf")',
		success() {
			console.log("宋体加载成功")
		},
		fail(err) {
			console.log("宋体加载失败", err);
		}
	})
}

function stsongtiscblackjt() {
	//引入外部字体
	uni.loadFontFace({
		family: 'FZSSJW',
		source: 'url("https://images.xmojiang.com/FZSSJW.TTF")',
		success() {
			console.log("简体宋体加载成功")
		},
		fail(err) {
			console.log("简体宋体加载失败", err);
		}
	})
}

function MontserratLigh() {
	//引入外部字体
	uni.loadFontFace({
		family: 'Montserrat-Ligh',
		source: 'url("https://images.xmojiang.com/Montserrat-Light.otf")',
		success() {
			console.log("简体宋体加载成功00")
		},
		fail(err) {
			console.log("简体宋体加载失败00", err);
		}
	})
}


function makePhoneCall(phone) {
	uni.makePhoneCall({
		phoneNumber: phone //仅为示例
	});
}

function filterList(arr, indexes) {
	let info = arr.filter(item => {
		return item.value == indexes
	})
	return info.length?info[0].name:'';
}


function randomNum(len, radix) {
  const chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.split('')
  const uuid = []
  radix = radix || chars.length

  if (len) {
    // Compact form
    for (let i = 0; i < len; i++) uuid[i] = chars[0 | Math.random() * radix ]
  } else {
    // rfc4122, version 4 form
    let r

    // rfc4122 requires these characters
    uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-'
    uuid[14] = '4'

    // Fill in random data.  At i==19 set the high bits of clock sequence as
    // per rfc4122, sec. 4.1.5
    for (let i = 0; i < 36; i++) {
      if (!uuid[i]) {
        r = 0 | Math.random() * 16
        uuid[i] = chars[(i === 19) ? (r & 0x3) | 0x8 : r]
      }
    }
  }
  return uuid.join('') + new Date().getTime()
}

function isNullData(data) {
	if (data !== '' && data !== null && typeof data !== 'undefined' && data !== '{}' && data !== {} && data !==
		'[]' && data !== 'null' && data != '无') {
		return false;
	}
	return true;
}
function getTransformData(data,defaultValue = '') {
	if (isNullData(data)) {
		return defaultValue
	}
	return data;
}

export {
	filterList,//筛选数组中的内容
	payStatelist,//支付状态
	orderStatelist,//订单状态
	payWaystringlist,//支付方式
	serviceTypeList,// 服务类型
	statusTypeList,// 核销状态
	classipad,
	navtop,
	customBar,
	deepCopy,
	jumpToLogin,
	getDate,
	timeToDate,
	formatDatetime,
	currentMonth,
	currentTime,
	formatMoney,
	/* errorToBack, */
	successToShow,
	throttle,
	errorToShow,
	time2date,
	isPhoneNumber,
	isInArray,
	loadToShow,
	loadToHide,
	navigateTo,
	redirectTo,
	switchTab,
	modelShow,
	builderUrlParams,
	isWeiXinBrowser,
	dateformat,
	getQueryString,
	timeToDateObj,
	moneySum,
	moneySub,
	shareParameterEncode,
	shareParameterDecode,
	SourceHanSansCN,
	SourceHanSerifCN,
	stsongtiscblack,
	stsongtiscblackjt,
	MontserratLigh,
	reLaunch,
	navigateBack,
	makePhoneCall,
	payTypelist,
	randomNum,//登录接口传值
	setNavigationBarTitle,
	timeformat,
	isNullData,
	getTransformData
}
