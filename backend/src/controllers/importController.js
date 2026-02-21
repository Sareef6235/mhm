import { fetchSheets, fetchSheetValues, transformSheetData } from '../services/googleSheetsService.js';

export async function previewSheets(req, res) {
  const { sheetId } = req.body;
  const sheets = await fetchSheets(sheetId);
  res.json({ sheets });
}

export async function previewHeaders(req, res) {
  const { sheetId, sheetName } = req.body;
  const rows = await fetchSheetValues(sheetId, sheetName);
  res.json({ headers: rows[0] || [], preview: rows.slice(1, 6) });
}

export async function transform(req, res) {
  const { sheetId, sheetName, mapping, subjectColumns } = req.body;
  const rows = await fetchSheetValues(sheetId, sheetName);
  const normalized = transformSheetData(rows, mapping, subjectColumns);
  res.json({ data: normalized });
}
