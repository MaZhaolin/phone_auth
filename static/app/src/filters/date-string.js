export default (time, isDate = false) => {
    return isDate ? new Date(time).toLocaleDateString().split('/').join('-') :
        new Date(time).toLocaleString("zh-CN", {hour12: false}).split('/').join('-');
}
