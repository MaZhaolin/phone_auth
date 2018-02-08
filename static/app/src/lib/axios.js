import axios from 'axios'

var instance = axios.create({
  baseURL: config.site_url,
  headers:{
      'Content-type': 'application/x-www-form-urlencoded'
  },
  transformRequest: [function (data) {
    let ret = ''
    for (let it in data) {
      ret += encodeURIComponent(it) + '=' + encodeURIComponent(data[it]) + '&'
    }
    return ret
  }],
})


export default instance;
