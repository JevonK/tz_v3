   module.exports = {
	    lintOnSave:false,
	    devServer:{
	    	// 关闭eslint语法验证
	    	overlay:{
	    		warning:false,
	    		errors:false
	    	}
	    },
        publicPath: process.env.NODE_ENV === 'production' ? './' : './'  
   };  