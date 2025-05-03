import * as XLSX from 'xlsx'

/**
 * Exports data to Excel file
 * @param {Array} data - Array of objects to export
 * @param {string} fileName - Name of the file without extension
 */
export const exportToExcel = (data, fileName) => {
  // Convert data to worksheet
  const worksheet = XLSX.utils.json_to_sheet(data)
  
  // Create workbook
  const workbook = XLSX.utils.book_new()
  XLSX.utils.book_append_sheet(workbook, worksheet, 'Sheet1')
  
  // Generate Excel file
  XLSX.writeFile(workbook, `${fileName}.xlsx`)
} 