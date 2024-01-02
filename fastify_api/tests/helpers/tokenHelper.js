module.exports = {
  getHeader: () => {
    return {
      Authorization: `Bearer ${process.env.TEMP_TOKEN}`
    }
  }
}