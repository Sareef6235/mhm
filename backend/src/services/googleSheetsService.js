import axios from 'axios';
import { env } from '../config/env.js';

const base = 'https://sheets.googleapis.com/v4/spreadsheets';

export async function fetchSheets(sheetId) {
  const { data } = await axios.get(`${base}/${sheetId}`, {
    params: { key: env.googleSheetsApiKey }
  });
  return data.sheets?.map((s) => s.properties.title) || [];
}

export async function fetchSheetValues(sheetId, sheetName) {
  const { data } = await axios.get(`${base}/${sheetId}/values/${encodeURIComponent(sheetName)}`, {
    params: { key: env.googleSheetsApiKey }
  });
  return data.values || [];
}

export function transformSheetData(rows, mapping, subjectColumns = []) {
  if (!rows.length) return [];
  const [header, ...dataRows] = rows;

  const idx = (colName) => header.findIndex((h) => h === colName);

  return dataRows
    .filter((row) => row[idx(mapping.registerNumber)])
    .map((row) => ({
      registerNumber: row[idx(mapping.registerNumber)] || '',
      name: row[idx(mapping.name)] || '',
      school: row[idx(mapping.school)] || '',
      photo: row[idx(mapping.photo)] || '',
      dob: row[idx(mapping.dob)] || '',
      subjects: subjectColumns.map((col) => ({
        name: col,
        mark: Number(row[idx(col)] || 0)
      }))
    }));
}
