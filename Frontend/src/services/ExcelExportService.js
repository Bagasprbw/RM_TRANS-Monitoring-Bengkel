import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';

export default class ExcelExportService {
  /**
   * Export maintenance history to Excel
   * @param {Array} data - The history records
   * @param {Object} options - { categoryName, filters }
   */
  static async exportRiwayat(data, { categoryName, filters }) {
    const workbook = new ExcelJS.Workbook();
    const sheetName = categoryName || 'Semua Riwayat';
    const worksheet = workbook.addWorksheet(sheetName);

    // Styling helpers
    const headerStyle = {
      font: { bold: true, color: { argb: 'FFFFFF' }, size: 11 },
      fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: '3E3D90' } },
      alignment: { horizontal: 'center', vertical: 'middle' },
      border: {
        top: { style: 'thin' },
        left: { style: 'thin' },
        bottom: { style: 'thin' },
        right: { style: 'thin' }
      }
    };

    const cellStyle = {
      alignment: { vertical: 'middle' },
      border: {
        top: { style: 'thin' },
        left: { style: 'thin' },
        bottom: { style: 'thin' },
        right: { style: 'thin' }
      }
    };

    // Determine layout based on category
    const normalizedCat = (categoryName || '').toLowerCase();

    if (normalizedCat.includes('ban')) {
      this.setupBanLayout(worksheet, data, headerStyle, cellStyle, filters);
    } else if (normalizedCat.includes('filter')) {
      this.setupFilterLayout(worksheet, data, headerStyle, cellStyle, filters);
    } else if (normalizedCat.includes('oli')) {
      // If category is "Oli", we try to show both layouts if relevant data exists
      // or a combined layout that handles Mesin, Transmisi, Gardan.
      this.setupCombinedOliLayout(worksheet, data, headerStyle, cellStyle, filters);
    } else if (normalizedCat.includes('accu') || normalizedCat.includes('aki')) {
      this.setupAccuLayout(worksheet, data, headerStyle, cellStyle, filters);
    } else {
      this.setupDefaultLayout(worksheet, data, headerStyle, cellStyle, filters);
    }

    // Generate buffer
    const buffer = await workbook.xlsx.writeBuffer();
    const fileName = `Riwayat_${sheetName.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.xlsx`;
    saveAs(new Blob([buffer]), fileName);
  }

  static setupBanLayout(ws, data, hStyle, cStyle, filters) {
    ws.mergeCells('A1:M1');
    ws.getCell('A1').value = 'BAN TERPASANG';
    ws.getCell('A1').style = { ...hStyle, font: { ...hStyle.font, size: 14 } };
    
    // Add Period Subtitle if exists
    if (filters?.date_from || filters?.date_to) {
      ws.mergeCells('A2:M2');
      ws.getCell('A2').value = `Periode: ${this.formatDate(filters.date_from)} s/d ${this.formatDate(filters.date_to)}`;
      ws.getCell('A2').style = { ...hStyle, fill: { ...hStyle.fill, fgColor: { argb: '7C3AED' } }, font: { ...hStyle.font, size: 10 } };
    }

    const headers = [
      'NO', 'NOPOL', 'JENIS KENDARAAN', 'JENIS (ORI/VULK)', 'SUPLIYER', 'MERK/TYPE/UK', 
      'NO SERI', 'NO STAMP', 'TGL PASANG', 'TGL LEPAS', 
      'SPEDOMETER PEMASANGAN', 'BAN BEKAS', 'HARGA'
    ];

    // Grouping by position
    const groups = {};
    data.forEach(item => {
      const pos = item.komponen?.nama_komponen || 'LAINNYA';
      if (!groups[pos]) groups[pos] = [];
      groups[pos].push(item);
    });

    let currentRow = 2;
    Object.keys(groups).forEach(pos => {
      // Add Position Header
      ws.mergeCells(`A${currentRow}:M${currentRow}`);
      const posCell = ws.getCell(`A${currentRow}`);
      posCell.value = pos.toUpperCase();
      posCell.style = { ...hStyle, fill: { ...hStyle.fill, fgColor: { argb: '7C3AED' } } }; // Purple for position header
      currentRow++;

      // Add Table Headers
      ws.getRow(currentRow).values = headers;
      ws.getRow(currentRow).eachCell((cell) => { cell.style = hStyle; });
      currentRow++;

      // Add Data
      groups[pos].forEach((row, docIndex) => {
        const detail = row.detail_komponen || {};
        const rowData = [
          docIndex + 1,
          row.komponen?.monitoring?.armada?.nopol || '-',
          row.komponen?.monitoring?.armada?.jenis?.nama_jenis || '-',
          detail.jenis_ban || '-',
          detail.pemasok || '-',
          `${detail.merk_tipe || '-'}${detail.ukuran ? ' / ' + detail.ukuran : ''}`,
          detail.nomor_seri || '-',
          detail.nomor_stamp || '-',
          this.formatDate(detail.tanggal_pemasangan),
          this.formatDate(detail.tanggal_pelepasan),
          (detail.km_pemasangan || row.km_saat_selesai || 0).toLocaleString(),
          detail.status_ban_bekas || '-',
          detail.harga || 0
        ];
        const r = ws.getRow(currentRow);
        r.values = rowData;
        r.eachCell((cell) => { cell.style = cStyle; });
        r.getCell(13).numFmt = '#,##0';
        currentRow++;
      });
      
      currentRow++; // Spacer row
    });

    ws.columns = [
      { width: 5 }, { width: 15 }, { width: 25 }, { width: 15 }, { width: 20 }, { width: 20 },
      { width: 15 }, { width: 15 }, { width: 15 }, { width: 15 }, { width: 22 },
      { width: 15 }, { width: 15 }
    ];
  }

  static setupFilterLayout(ws, data, hStyle, cStyle, filters) {
    ws.mergeCells('A1:D1');
    ws.getCell('A1').value = 'Monitoring Filter Udara';
    ws.getCell('A1').style = { ...hStyle, font: { ...hStyle.font, size: 14 } };

    if (filters?.date_from || filters?.date_to) {
      ws.mergeCells('A2:D2');
      ws.getCell('A2').value = `Periode: ${this.formatDate(filters.date_from)} s/d ${this.formatDate(filters.date_to)}`;
      ws.getCell('A2').style = { ...hStyle, font: { ...hStyle.font, size: 10 } };
    }

    const headers = ['NO', 'NOPOL', 'JENIS ARMADA', 'PENGGANTIAN TERAKHIR'];
    const startRow = (filters?.date_from || filters?.date_to) ? 3 : 2;
    ws.getRow(startRow).values = headers;
    ws.getRow(startRow).eachCell((cell) => { cell.style = hStyle; });

    data.forEach((row, i) => {
      const r = ws.addRow([
        i + 1,
        row.komponen?.monitoring?.armada?.nopol || '-',
        row.komponen?.monitoring?.armada?.jenis?.nama_jenis || '-',
        this.formatDate(row.tanggal_selesai)
      ]);
      r.eachCell((cell) => { cell.style = cStyle; });
    });

    ws.columns = [{ width: 5 }, { width: 15 }, { width: 25 }, { width: 25 }];
  }

  static setupCombinedOliLayout(ws, data, hStyle, cStyle, filters) {
    // 1. OLI MESIN SECTION
    const dataMesin = data.filter(d => {
      const n = (d.komponen?.nama_komponen || '').toLowerCase();
      return n.includes('mesin') || n.includes('engine');
    });
    const dataTransGardan = data.filter(d => {
      const n = (d.komponen?.nama_komponen || '').toLowerCase();
      return n.includes('transmisi') || n.includes('gardan');
    });

    let currentRow = 1;

    // Add Period at the top if exists
    if (filters?.date_from || filters?.date_to) {
      ws.mergeCells(`A${currentRow}:G${currentRow}`);
      ws.getCell(`A${currentRow}`).value = `Periode Export: ${this.formatDate(filters.date_from)} s/d ${this.formatDate(filters.date_to)}`;
      ws.getCell(`A${currentRow}`).style = { ...hStyle, font: { ...hStyle.font, size: 11 } };
      currentRow++;
    }

    if (dataMesin.length > 0) {
      ws.mergeCells(`A${currentRow}:F${currentRow}`);
      ws.getCell(`A${currentRow}`).value = 'MONITORING TAP OLI MESIN';
      ws.getCell(`A${currentRow}`).style = { ...hStyle, font: { ...hStyle.font, size: 14 }, fill: { ...hStyle.fill, fgColor: { argb: '059669' } } };
      currentRow++;

      const headersMesin = ['NO', 'NOPOL', 'SPEDO METER', 'JENIS KENDARAAN', 'TANGGAL GANTI', 'CATATAN'];
      ws.getRow(currentRow).values = headersMesin;
      ws.getRow(currentRow).eachCell((cell) => { cell.style = hStyle; });
      currentRow++;

      dataMesin.forEach((row, i) => {
        const r = ws.addRow([
          i + 1,
          row.komponen?.monitoring?.armada?.nopol || '-',
          (row.km_saat_selesai || 0).toLocaleString() + ' KM',
          row.komponen?.monitoring?.armada?.jenis?.nama_jenis || '-',
          this.formatDate(row.tanggal_selesai),
          row.catatan || '-'
        ]);
        r.eachCell((cell) => { cell.style = cStyle; });
        currentRow++;
      });
      currentRow += 2; // Gap
    }

    if (dataTransGardan.length > 0) {
      ws.mergeCells(`A${currentRow}:G${currentRow}`);
      ws.getCell(`A${currentRow}`).value = 'MONITORING TAP OLI TRANSMISI & OLI GARDAN';
      ws.getCell(`A${currentRow}`).style = { ...hStyle, font: { ...hStyle.font, size: 14 } };
      currentRow++;

      // Sub headers
      ws.mergeCells(`D${currentRow}:E${currentRow}`);
      ws.getCell(`D${currentRow}`).value = 'JUMLAH OLI | liter?';
      ws.mergeCells(`F${currentRow}:G${currentRow}`);
      ws.getCell(`F${currentRow}`).value = 'PENGGANTIAN OLI TERAKHIR';
      [ws.getCell(`D${currentRow}`), ws.getCell(`F${currentRow}`)].forEach(c => c.style = hStyle);
      currentRow++;

      const headers = ['NO', 'NOPOL', 'JENIS KENDARAAN', 'TRANSMISI', 'GARDAN', 'OLI TRANSMISI', 'OLI GARDAN'];
      ws.getRow(currentRow).values = headers;
      ws.getRow(currentRow).eachCell((cell) => { cell.style = hStyle; });
      const grouped = {};
      dataTransGardan.forEach(row => {
        const nopol = row.komponen?.monitoring?.armada?.nopol || '-';
        if (!grouped[nopol]) {
          grouped[nopol] = {
            nopol: nopol,
            jenis: row.komponen?.monitoring?.armada?.jenis?.nama_jenis || '-',
            transmisi_liter: '-',
            gardan_liter: '-',
            transmisi_tgl: '-',
            gardan_tgl: '-'
          };
        }
        
        const name = (row.komponen?.nama_komponen || '').toLowerCase();
        if (name.includes('transmisi')) {
          // Newest first, so only set if currently empty
          if (grouped[nopol].transmisi_tgl === '-') {
            grouped[nopol].transmisi_liter = row.jumlah_liter || '-';
            grouped[nopol].transmisi_tgl = this.formatDate(row.tanggal_selesai);
          }
        } else if (name.includes('gardan')) {
          if (grouped[nopol].gardan_tgl === '-') {
            grouped[nopol].gardan_liter = row.jumlah_liter || '-';
            grouped[nopol].gardan_tgl = this.formatDate(row.tanggal_selesai);
          }
        }
      });

      Object.values(grouped).forEach((row, i) => {
        const r = ws.addRow([
          i + 1,
          row.nopol,
          row.jenis,
          row.transmisi_liter,
          row.gardan_liter,
          row.transmisi_tgl,
          row.gardan_tgl
        ]);
        r.eachCell((cell) => { cell.style = cStyle; });
        currentRow++;
      });
    }

    ws.columns = [
      { width: 5 }, { width: 15 }, { width: 25 }, 
      { width: 14 }, { width: 14 }, { width: 20 }, { width: 20 }
    ];
  }

  static setupAccuLayout(ws, data, hStyle, cStyle, filters) {
    ws.mergeCells('A1:I1');
    ws.getCell('A1').value = 'MONITORING PENGGUNAAN ACCU';
    ws.getCell('A1').style = { ...hStyle, font: { ...hStyle.font, size: 14 } };

    let currentRow = 2;
    if (filters?.date_from || filters?.date_to) {
      ws.mergeCells(`A${currentRow}:I${currentRow}`);
      ws.getCell(`A${currentRow}`).value = `Periode: ${this.formatDate(filters.date_from)} s/d ${this.formatDate(filters.date_to)}`;
      ws.getCell(`A${currentRow}`).style = { ...hStyle, font: { ...hStyle.font, size: 11 } };
      currentRow++;
    }

    const startSecondary = currentRow;
    ws.mergeCells(`D${startSecondary}:E${startSecondary}`); ws.getCell(`D${startSecondary}`).value = 'NO SERI';
    ws.mergeCells(`F${startSecondary}:G${startSecondary}`); ws.getCell(`F${startSecondary}`).value = 'TGL PEMASANGAN';
    ws.mergeCells(`H${startSecondary}:I${startSecondary}`); ws.getCell(`H${startSecondary}`).value = 'TGL HABIS';
    [ws.getCell(`D${startSecondary}`), ws.getCell(`F${startSecondary}`), ws.getCell(`H${startSecondary}`)].forEach(c => c.style = hStyle);
    currentRow++;

    const headers = ['NO', 'NOPOL', 'JENIS KENDARAAN', 'KIRI', 'KANAN', 'KIRI', 'KANAN', 'KIRI', 'KANAN'];
    ws.getRow(currentRow).values = headers;
    ws.getRow(currentRow).eachCell((cell) => { cell.style = hStyle; });
    currentRow++;

    const grouped = {};
    data.forEach(row => {
      const nopol = row.komponen?.monitoring?.armada?.nopol || '-';
      if (!grouped[nopol]) {
        grouped[nopol] = {
          nopol: nopol,
          jenis: row.komponen?.monitoring?.armada?.jenis?.nama_jenis || '-',
          kiri_sn: '-',
          kanan_sn: '-',
          kiri_pasang: '-',
          kanan_pasang: '-',
          kiri_habis: '-',
          kanan_habis: '-'
        };
      }

      const detail = row.detail_komponen || {};
      const name = (row.komponen?.nama_komponen || '').toLowerCase();
      if (name.includes('kiri')) {
        if (grouped[nopol].kiri_habis === '-') {
          grouped[nopol].kiri_sn = detail.nomor_seri || '-';
          grouped[nopol].kiri_pasang = this.formatDate(detail.tanggal_pemasangan);
          grouped[nopol].kiri_habis = this.formatDate(row.tanggal_selesai);
        }
      } else if (name.includes('kanan')) {
        if (grouped[nopol].kanan_habis === '-') {
          grouped[nopol].kanan_sn = detail.nomor_seri || '-';
          grouped[nopol].kanan_pasang = this.formatDate(detail.tanggal_pemasangan);
          grouped[nopol].kanan_habis = this.formatDate(row.tanggal_selesai);
        }
      }
    });

    Object.values(grouped).forEach((row, i) => {
      const r = ws.addRow([
        i + 1,
        row.nopol,
        row.jenis,
        row.kiri_sn,
        row.kanan_sn,
        row.kiri_pasang,
        row.kanan_pasang,
        row.kiri_habis,
        row.kanan_habis,
      ]);
      r.eachCell((cell) => { cell.style = cStyle; });
    });

    ws.columns = [
      { width: 5 }, { width: 15 }, { width: 25 }, { width: 15 }, { width: 15 },
      { width: 15 }, { width: 15 }, { width: 15 }, { width: 15 }
    ];
  }

  static setupDefaultLayout(ws, data, hStyle, cStyle, filters) {
    const headers = ['NO', 'TANGGAL', 'NOPOL', 'JENIS KENDARAAN', 'KOMPONEN', 'KATEGORI', 'KM RECORD', 'DETAIL', 'CATATAN'];
    
    let currentRow = 1;
    if (filters?.date_from || filters?.date_to) {
      ws.mergeCells(`A${currentRow}:I${currentRow}`);
      ws.getCell(`A${currentRow}`).value = `LAPORAN RIWAYAT PERAWATAN (${this.formatDate(filters.date_from)} s/d ${this.formatDate(filters.date_to)})`;
      ws.getCell(`A${currentRow}`).style = { ...hStyle, font: { ...hStyle.font, size: 12 } };
      currentRow++;
    }

    ws.getRow(currentRow).values = headers;
    ws.getRow(currentRow).eachCell((cell) => { cell.style = hStyle; });
    currentRow++;

    data.forEach((row, i) => {
      const detail = row.detail_komponen || {};
      let detailText = '';
      if (detail.nomor_seri) detailText += `SN: ${detail.nomor_seri}; `;
      if (detail.merk_tipe) detailText += `Merk: ${detail.merk_tipe}; `;

      const r = ws.addRow([
        i + 1,
        this.formatDate(row.tanggal_selesai),
        row.komponen?.monitoring?.armada?.nopol || '-',
        row.komponen?.monitoring?.armada?.jenis?.nama_jenis || '-',
        row.komponen?.nama_komponen || '-',
        row.komponen?.kategori?.nama_kategori || '-',
        (row.km_saat_selesai || 0).toLocaleString(),
        detailText || '-',
        row.catatan || '-'
      ]);
      r.eachCell((cell) => { cell.style = cStyle; });
    });

    ws.columns = [
      { width: 5 }, { width: 15 }, { width: 15 }, { width: 25 }, { width: 25 }, 
      { width: 20 }, { width: 15 }, { width: 30 }, { width: 30 }
    ];
  }

  static formatDate(date) {
    if (!date) return '-';
    // Handle YYYY-MM-DD format to avoid UTC issues
    const d = date.split('T')[0].split('-');
    if (d.length === 3) {
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
      return `${d[2]} ${months[parseInt(d[1]) - 1]} ${d[0]}`;
    }
    return date;
  }
}
