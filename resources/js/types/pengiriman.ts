export interface pengiriman {
    id: string;
    no_resi: string;
    penerima: string;
    alamat: string;
    kurir: string;
    status: "dikirim" | "terkirim";
  }