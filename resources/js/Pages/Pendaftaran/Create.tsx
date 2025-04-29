import React, { useState, useEffect } from "react"
import { useForm, usePage, Head } from "@inertiajs/react" // Import Inertia hooks
import { format } from "date-fns"
import { CalendarIcon, Check, ChevronRight, Download, Loader2 } from "lucide-react"
import { cn } from "@/lib/utils"

// --- UI Components (ensure these are installed via Shadcn CLI) ---
import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"
import { Textarea } from "@/components/ui/textarea"
import { Calendar } from "@/components/ui/calendar"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { Switch } from "@/components/ui/switch"
import { Badge } from "@/components/ui/badge"
import { Separator } from "@/components/ui/separator"
import { useToast } from "@/components/ui/use-toast" // Make sure Toaster is added in your main layout
import { Toaster } from "@/components/ui/toaster" // Import Toaster

// --- Define Types from Controller Props ---
// (These should ideally match the structure passed by Inertia::render)
interface OptionType {
    value: string
    label: string
}
interface DokumenType {
    id: string
    nama: string
    keterangan: string
    template_url: string | null
}

interface PageProps {
    gelombangPendaftaranId: string
    tahunPendaftaran: number
    requiredDokumen: DokumenType[]
    options: {
        negara: OptionType[]
        jenis_kelamin: OptionType[]
        pendidikan_terakhir: OptionType[]
        status_kuliah: OptionType[]
        mulai_mengaji: OptionType[]
        bahasa_makna: OptionType[]
        golongan_darah: OptionType[]
        ukuran_baju: OptionType[]
        status_pernikahan: OptionType[]
        status_tinggal: OptionType[]
        status_orang_tua: OptionType[]
        hubungan_wali: OptionType[]
        jenis_sim: OptionType[]
        provinsi: Record<string, string> // Keep as object for easier lookup from ID
        daerah_sambung: Record<string, string> // Keep as object
    }
    statusOrangTuaHidup: string
    hubunganWaliOrangTua: string
    negaraIndonesia: string
    errors: Record<string, string> // Validation errors from Laravel
}

// Define the steps
const STEPS = [
    { id: 1, title: "Data Diri & Alamat" },
    { id: 2, title: "Informasi Sambung & Pendidikan" },
    { id: 3, title: "Informasi Tambahan & Wali" },
    { id: 4, title: "Upload Dokumen" },
]

// --- Main Component ---
const PendaftaranCreate: React.FC = () => {
    const { props } = usePage<PageProps>() // Get props passed from controller
    const { toast } = useToast()

    const {
        gelombangPendaftaranId,
        tahunPendaftaran,
        requiredDokumen,
        options,
        statusOrangTuaHidup,
        hubunganWaliOrangTua,
        negaraIndonesia,
    } = props

    // State for the current step
    const [currentStep, setCurrentStep] = useState(1)

    // State for dependent dropdowns (store as arrays for easier mapping)
    const [kotaOptions, setKotaOptions] = useState<OptionType[]>([])
    const [kecamatanOptions, setKecamatanOptions] = useState<OptionType[]>([])
    const [kelurahanOptions, setKelurahanOptions] = useState<OptionType[]>([])

    // Convert options.provinsi and options.daerah_sambung to array format for Select
    const provinsiOptions = Object.entries(options.provinsi).map(([value, label]) => ({ value, label }))
    const daerahSambungOptions = Object.entries(options.daerah_sambung).map(([value, label]) => ({ value, label }))

    // --- Inertia Form Hook ---
    const { data, setData, post, processing, errors, reset, progress } = useForm({
        // Initialize form fields based on your controller's validation rules
        // Match the keys used in your Laravel validation rules
        gelombang_pendaftaran_id: gelombangPendaftaranId,
        nama: "",
        nama_panggilan: "",
        jenis_kelamin: "",
        nomor_telepon: "",
        email: "",
        tempat_lahir: "",
        tanggal_lahir: undefined as Date | undefined, // Use Date for Calendar component
        kewarganegaraan: negaraIndonesia,
        nomor_induk_kependudukan: "",
        nomor_kartu_keluarga: "",
        nomor_passport: "",
        alamat: "",
        rt: "",
        rw: "",
        provinsi_id: "",
        kota_id: "",
        kecamatan_id: "",
        kelurahan_id: "",
        alamat_non_indo: "",
        city: "",
        state_province: "",
        kode_pos: "",
        kelompok_sambung: "",
        desa_sambung: "",
        daerah_sambung_id: "",
        status_mubaligh: false,
        pernah_mondok: false,
        nama_pondok_sebelumnya: "",
        lama_mondok_sebelumnya: undefined as number | undefined,
        pendidikan_terakhir: "",
        jurusan: "",
        universitas: "",
        program_studi: "",
        angkatan_kuliah: undefined as number | undefined,
        status_kuliah: "",
        mulai_mengaji: "",
        bahasa_makna: "",
        bahasa_harian: [] as string[],
        keahlian: [] as string[],
        hobi: [] as string[],
        sim: [] as string[],
        tinggi_badan: undefined as number | undefined,
        berat_badan: undefined as number | undefined,
        riwayat_sakit: "",
        alergi: "",
        golongan_darah: "",
        ukuran_baju: "",
        status_pernikahan: "",
        status_tinggal: "",
        anak_nomor: undefined as number | undefined,
        jumlah_saudara: undefined as number | undefined,
        nama_ayah: "",
        status_ayah: statusOrangTuaHidup, // Default status
        nomor_telepon_ayah: "",
        tempat_lahir_ayah: "",
        tanggal_lahir_ayah: undefined as Date | undefined,
        pekerjaan_ayah: "",
        dapukan_ayah: "",
        alamat_ayah: "",
        kelompok_sambung_ayah: "",
        desa_sambung_ayah: "",
        daerah_sambung_ayah_id: "",
        nama_ibu: "",
        status_ibu: statusOrangTuaHidup, // Default status
        nomor_telepon_ibu: "",
        tempat_lahir_ibu: "",
        tanggal_lahir_ibu: undefined as Date | undefined,
        pekerjaan_ibu: "",
        dapukan_ibu: "",
        alamat_ibu: "",
        kelompok_sambung_ibu: "",
        desa_sambung_ibu: "",
        daerah_sambung_ibu_id: "",
        hubungan_wali: hubunganWaliOrangTua, // Default status
        nama_wali: "",
        nomor_telepon_wali: "",
        pekerjaan_wali: "",
        dapukan_wali: "",
        alamat_wali: "",
        kelompok_sambung_wali: "",
        desa_sambung_wali: "",
        daerah_sambung_wali_id: "",
        // --- IMPORTANT: Structure for file uploads ---
        dokumen: requiredDokumen.map((doc) => ({
            dokumen_pendaftaran_id: doc.id,
            file: null as File | null, // Store File objects here
        })),
    })

    // --- Watch for changes in key fields ---
    const kewarganegaraan = data.kewarganegaraan
    const provinsiId = data.provinsi_id
    const kotaId = data.kota_id
    const kecamatanId = data.kecamatan_id
    const statusMubaligh = data.status_mubaligh
    const pernahMondok = data.pernah_mondok
    const statusAyah = data.status_ayah
    const statusIbu = data.status_ibu
    const hubunganWali = data.hubungan_wali

    // --- Effects ---

    // Effect for status_mubaligh and pernah_mondok relationship
    useEffect(() => {
        if (statusMubaligh) {
            setData("pernah_mondok", true)
        }
    }, [statusMubaligh, setData])

    // Fetch kota options when provinsi changes
    useEffect(() => {
        if (provinsiId) {
            const fetchKota = async () => {
                try {
                    // IMPORTANT: Update API path to match your Laravel routes
                    const response = await fetch(`/api/options/kota?provinsi_id=${provinsiId}`)
                    const apiData: Record<string, string> = await response.json()
                    const formattedOptions = Object.entries(apiData).map(([value, label]) => ({ value, label }))
                    setKotaOptions(formattedOptions)
                    // Reset dependent fields using setData
                    setData((prev) => ({
                        ...prev,
                        kota_id: "",
                        kecamatan_id: "",
                        kelurahan_id: "",
                    }))
                    setKecamatanOptions([])
                    setKelurahanOptions([])
                } catch (error) {
                    console.error("Error fetching kota:", error)
                    toast({ title: "Error", description: "Gagal memuat data kota.", variant: "destructive" })
                }
            }
            fetchKota()
        } else {
            setKotaOptions([])
            setKecamatanOptions([])
            setKelurahanOptions([])
        }
    }, [provinsiId, setData, toast])

    // Fetch kecamatan options when kota changes
    useEffect(() => {
        if (kotaId) {
            const fetchKecamatan = async () => {
                try {
                    // IMPORTANT: Update API path
                    const response = await fetch(`/api/options/kecamatan?kota_id=${kotaId}`)
                    const apiData: Record<string, string> = await response.json()
                    const formattedOptions = Object.entries(apiData).map(([value, label]) => ({ value, label }))
                    setKecamatanOptions(formattedOptions)
                    setData((prev) => ({ ...prev, kecamatan_id: "", kelurahan_id: "" }))
                    setKelurahanOptions([])
                } catch (error) {
                    console.error("Error fetching kecamatan:", error)
                    toast({ title: "Error", description: "Gagal memuat data kecamatan.", variant: "destructive" })
                }
            }
            fetchKecamatan()
        } else {
            setKecamatanOptions([])
        }
    }, [kotaId, setData, toast])

    // Fetch kelurahan options when kecamatan changes
    useEffect(() => {
        if (kecamatanId) {
            const fetchKelurahan = async () => {
                try {
                    // IMPORTANT: Update API path
                    const response = await fetch(`/api/options/kelurahan?kecamatan_id=${kecamatanId}`)
                    const apiData: Record<string, string> = await response.json()
                    const formattedOptions = Object.entries(apiData).map(([value, label]) => ({ value, label }))
                    setKelurahanOptions(formattedOptions)
                    setData("kelurahan_id", "")
                } catch (error) {
                    console.error("Error fetching kelurahan:", error)
                    toast({ title: "Error", description: "Gagal memuat data kelurahan.", variant: "destructive" })
                }
            }
            fetchKelurahan()
        } else {
            setKelurahanOptions([])
        }
    }, [kecamatanId, setData, toast])

    // Effect for kewarganegaraan changes
    useEffect(() => {
        if (kewarganegaraan === negaraIndonesia) {
            setData((prev) => ({
                ...prev,
                alamat_non_indo: "",
                city: "",
                state_province: "",
            }))
        } else {
            setData((prev) => ({
                ...prev,
                alamat: "",
                rt: "",
                rw: "",
                provinsi_id: "",
                kota_id: "",
                kecamatan_id: "",
                kelurahan_id: "",
                nomor_induk_kependudukan: "",
                nomor_kartu_keluarga: "",
            }))
            setKotaOptions([])
            setKecamatanOptions([])
            setKelurahanOptions([])
        }
    }, [kewarganegaraan, setData, negaraIndonesia])

    // --- Handlers ---

    // Handle file change and update Inertia form state
    const handleFileChange = (dokumenId: string, file: File | null, index: number) => {
        // Create a mutable copy of the dokumen array
        const newDokumenState = [...data.dokumen];
        // Update the specific item
        newDokumenState[index] = {
            ...newDokumenState[index],
            file: file // Update the file object
        };
        // Set the updated array back to the form state
        setData('dokumen', newDokumenState);

        // Basic client-side validation (optional, but good UX)
        if (file && file.size > 5 * 1024 * 1024) {
            toast({ title: "File Error", description: `Ukuran file ${file.name} terlalu besar (Maks 5MB).`, variant: "destructive" });
            // Optionally clear the file if invalid
            newDokumenState[index] = { ...newDokumenState[index], file: null };
            setData('dokumen', newDokumenState);
        } else if (file && !["application/pdf", "image/jpeg", "image/jpg", "image/png"].includes(file.type)) {
            toast({ title: "File Error", description: `Format file ${file.name} tidak valid (PDF, JPG, PNG).`, variant: "destructive" });
            // Optionally clear the file if invalid
            newDokumenState[index] = { ...newDokumenState[index], file: null };
            setData('dokumen', newDokumenState);
        }
    };


    // Handle tag input
    const handleTagInput = (field: "bahasa_harian" | "keahlian" | "hobi", value: string) => {
        const trimmedValue = value.trim()
        if (trimmedValue && !data[field].includes(trimmedValue)) {
            setData(field, [...data[field], trimmedValue])
        }
    }

    const removeTag = (field: "bahasa_harian" | "keahlian" | "hobi", tag: string) => {
        setData(
            field,
            data[field].filter((t) => t !== tag),
        )
    }

    // Handle SIM selection
    const handleSimChange = (value: string) => {
        const currentSim = data.sim || []
        if (currentSim.includes(value)) {
            setData(
                "sim",
                currentSim.filter((s) => s !== value),
            )
        } else {
            setData("sim", [...currentSim, value])
        }
    }

    // Client-side validation check (simplified for step progression)
    const validateStep = () => {
        const currentValues = data
        let stepValid = true

        // Basic checks - more complex/conditional logic relies on backend errors after post
        if (currentStep === 1) {
            stepValid = !!currentValues.nama && !!currentValues.nama_panggilan && !!currentValues.jenis_kelamin &&
                !!currentValues.nomor_telepon && !!currentValues.email && !!currentValues.tempat_lahir &&
                !!currentValues.tanggal_lahir && !!currentValues.kewarganegaraan && !!currentValues.kode_pos;
            if (kewarganegaraan === negaraIndonesia) {
                stepValid = stepValid && !!currentValues.nomor_induk_kependudukan && !!currentValues.nomor_kartu_keluarga &&
                    !!currentValues.alamat && !!currentValues.rt && !!currentValues.rw && !!currentValues.provinsi_id &&
                    !!currentValues.kota_id && !!currentValues.kecamatan_id && !!currentValues.kelurahan_id;
            } else {
                stepValid = stepValid && !!currentValues.nomor_passport && !!currentValues.alamat_non_indo &&
                    !!currentValues.city && !!currentValues.state_province;
            }
        } else if (currentStep === 2) {
            stepValid = !!currentValues.kelompok_sambung && !!currentValues.desa_sambung && !!currentValues.daerah_sambung_id &&
                !!currentValues.pendidikan_terakhir && !!currentValues.universitas && !!currentValues.program_studi &&
                !!currentValues.angkatan_kuliah && !!currentValues.status_kuliah;
            if (pernahMondok) {
                stepValid = stepValid && !!currentValues.nama_pondok_sebelumnya && !!currentValues.lama_mondok_sebelumnya;
            }
        } else if (currentStep === 3) {
            stepValid = !!currentValues.mulai_mengaji && !!currentValues.bahasa_makna && currentValues.bahasa_harian.length > 0 &&
                !!currentValues.tinggi_badan && !!currentValues.berat_badan && !!currentValues.golongan_darah &&
                !!currentValues.ukuran_baju && !!currentValues.status_pernikahan && !!currentValues.status_tinggal &&
                !!currentValues.anak_nomor && currentValues.jumlah_saudara !== undefined &&
                !!currentValues.nama_ayah && !!currentValues.status_ayah && !!currentValues.nama_ibu &&
                !!currentValues.status_ibu && !!currentValues.hubungan_wali;

            if (statusAyah === statusOrangTuaHidup) {
                stepValid = stepValid && !!currentValues.nomor_telepon_ayah && !!currentValues.tempat_lahir_ayah &&
                    !!currentValues.tanggal_lahir_ayah && !!currentValues.pekerjaan_ayah && !!currentValues.dapukan_ayah &&
                    !!currentValues.alamat_ayah && !!currentValues.kelompok_sambung_ayah && !!currentValues.desa_sambung_ayah &&
                    !!currentValues.daerah_sambung_ayah_id;
            }
            if (statusIbu === statusOrangTuaHidup) {
                stepValid = stepValid && !!currentValues.nomor_telepon_ibu && !!currentValues.tempat_lahir_ibu &&
                    !!currentValues.tanggal_lahir_ibu && !!currentValues.pekerjaan_ibu && !!currentValues.dapukan_ibu &&
                    !!currentValues.alamat_ibu && !!currentValues.kelompok_sambung_ibu && !!currentValues.desa_sambung_ibu &&
                    !!currentValues.daerah_sambung_ibu_id;
            }
            if (hubunganWali !== hubunganWaliOrangTua) {
                stepValid = stepValid && !!currentValues.nama_wali && !!currentValues.nomor_telepon_wali &&
                    !!currentValues.pekerjaan_wali && !!currentValues.dapukan_wali && !!currentValues.alamat_wali;
            }

        } else if (currentStep === 4) {
            // Check if all required documents have a file attached
            stepValid = data.dokumen.every(doc => !!doc.file);
            if (!stepValid) {
                toast({ title: "Dokumen Belum Lengkap", description: "Mohon unggah semua dokumen yang diperlukan.", variant: "destructive" });
            }
        }


        if (!stepValid && currentStep !== 4) { // Show general error for steps 1-3 if fields are missing
            toast({ title: "Validasi Gagal", description: "Mohon lengkapi semua field yang bertanda * di langkah ini.", variant: "destructive" });
        }

        return stepValid
    }

    // Handle next step
    const handleNext = () => {
        if (validateStep()) {
            // Scroll to top might be useful here
            window.scrollTo(0, 0);
            setCurrentStep((prev) => Math.min(prev + 1, STEPS.length))
        }
    }

    // Handle previous step
    const handlePrevious = () => {
        window.scrollTo(0, 0); // Scroll to top
        setCurrentStep((prev) => Math.max(prev - 1, 1))
    }

    // Handle form submission using Inertia
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault()

        if (!validateStep()) {
            toast({ title: "Validasi Gagal", description: "Mohon periksa kembali data yang dimasukkan, terutama pada langkah terakhir.", variant: "destructive" })
            return;
        }

        // Prepare data for submission - Inertia's `post` handles FormData automatically
        const submitData = {
            ...data,
            // Convert Date objects to strings (YYYY-MM-DD) before submission if necessary
            // Inertia/Laravel might handle Date objects, but string format is safer
            tanggal_lahir: data.tanggal_lahir ? format(data.tanggal_lahir, "yyyy-MM-dd") : null,
            tanggal_lahir_ayah: data.tanggal_lahir_ayah ? format(data.tanggal_lahir_ayah, "yyyy-MM-dd") : null,
            tanggal_lahir_ibu: data.tanggal_lahir_ibu ? format(data.tanggal_lahir_ibu, "yyyy-MM-dd") : null,

            // Ensure numbers are numbers or null
            lama_mondok_sebelumnya: data.lama_mondok_sebelumnya || null,
            angkatan_kuliah: data.angkatan_kuliah || null,
            tinggi_badan: data.tinggi_badan || null,
            berat_badan: data.berat_badan || null,
            anak_nomor: data.anak_nomor || null,
            jumlah_saudara: data.jumlah_saudara !== undefined ? data.jumlah_saudara : null, // Handle 0
        };


        // IMPORTANT: Use Inertia's post method
        // Replace '/pendaftaran/santri' with route('pendaftaran.store') if using Ziggy
        post(route('pendaftaran.store'), { // Or '/pendaftaran/santri'
            data: submitData, // Pass prepared data explicitly if needed, often not required as `post` uses `data` state
            forceFormData: true, // Ensure it uses FormData due to file uploads
            onSuccess: () => {
                toast({ title: "Pendaftaran Berhasil", description: "Data Anda telah terkirim." })
                // No need for manual redirect, Inertia handles the controller's RedirectResponse
                reset(); // Optionally reset form fields after successful submission
            },
            onError: (errs) => {
                console.error("Submission Errors:", errs)
                toast({
                    title: "Pendaftaran Gagal",
                    description: "Terdapat kesalahan pada data yang Anda masukkan. Mohon periksa kembali.",
                    variant: "destructive",
                })
                // Find the first step with an error and navigate to it
                const errorKeys = Object.keys(errs);
                let errorStep = STEPS.length; // Default to last step
                for (let i = 0; i < STEPS.length; i++) {
                    const stepFields = getFieldsForStep(i + 1); // Helper function to get field names for a step
                    if (errorKeys.some(key => stepFields.includes(key.split('.')[0]))) { // Check base key for array fields like 'dokumen.0.file'
                        errorStep = i + 1;
                        break;
                    }
                }
                setCurrentStep(errorStep);
                window.scrollTo(0, 0); // Scroll to top after navigating to error step
            },
            onFinish: () => {
                // Any cleanup action after success or error
            }
        })
    }

    // Helper function to get field names associated with a step (adjust based on your fields per step)
    const getFieldsForStep = (stepNum: number): string[] => {
        if (stepNum === 1) return ["nama", "nama_panggilan", "jenis_kelamin", "nomor_telepon", "email", "tempat_lahir", "tanggal_lahir", "kewarganegaraan", "nomor_induk_kependudukan", "nomor_kartu_keluarga", "nomor_passport", "alamat", "rt", "rw", "provinsi_id", "kota_id", "kecamatan_id", "kelurahan_id", "alamat_non_indo", "city", "state_province", "kode_pos"];
        if (stepNum === 2) return ["kelompok_sambung", "desa_sambung", "daerah_sambung_id", "status_mubaligh", "pernah_mondok", "nama_pondok_sebelumnya", "lama_mondok_sebelumnya", "pendidikan_terakhir", "jurusan", "universitas", "program_studi", "angkatan_kuliah", "status_kuliah"];
        if (stepNum === 3) return ["mulai_mengaji", "bahasa_makna", "bahasa_harian", "keahlian", "hobi", "sim", "tinggi_badan", "berat_badan", "riwayat_sakit", "alergi", "golongan_darah", "ukuran_baju", "status_pernikahan", "status_tinggal", "anak_nomor", "jumlah_saudara", "nama_ayah", "status_ayah", "nomor_telepon_ayah", "tempat_lahir_ayah", "tanggal_lahir_ayah", "pekerjaan_ayah", "dapukan_ayah", "alamat_ayah", "kelompok_sambung_ayah", "desa_sambung_ayah", "daerah_sambung_ayah_id", "nama_ibu", "status_ibu", "nomor_telepon_ibu", "tempat_lahir_ibu", "tanggal_lahir_ibu", "pekerjaan_ibu", "dapukan_ibu", "alamat_ibu", "kelompok_sambung_ibu", "desa_sambung_ibu", "daerah_sambung_ibu_id", "hubungan_wali", "nama_wali", "nomor_telepon_wali", "pekerjaan_wali", "dapukan_wali", "alamat_wali", "kelompok_sambung_wali", "desa_sambung_wali", "daerah_sambung_wali_id"];
        if (stepNum === 4) return ["dokumen"]; // Special case for file array
        return [];
    }


    // --- Render Logic ---

    // Render step indicators
    const renderStepIndicators = () => (
        <div className="flex items-center justify-center mb-8 overflow-x-auto pb-2">
            {STEPS.map((step, index) => (
                <React.Fragment key={step.id}>
                    <div className="flex flex-col items-center flex-shrink-0 mx-2">
                        <div
                            className={cn(
                                "flex items-center justify-center rounded-full w-10 h-10 text-sm font-medium border transition-all",
                                currentStep === step.id
                                    ? "bg-emerald-500 text-white border-emerald-500" // Removed pulse for simplicity
                                    : currentStep > step.id
                                        ? "bg-emerald-100 text-emerald-700 border-emerald-500"
                                        : "bg-gray-100 text-gray-500 border-gray-300",
                            )}
                        >
                            {currentStep > step.id ? <Check className="h-5 w-5" /> : step.id}
                        </div>
                        <p className={cn("mt-2 text-xs text-center", currentStep === step.id ? "font-semibold text-emerald-600" : "text-gray-500")} style={{ maxWidth: '60px' }}>
                            {step.title}
                        </p>
                    </div>
                    {index < STEPS.length - 1 && (
                        <ChevronRight
                            className={cn("mx-1 h-4 w-4 self-start mt-3 flex-shrink-0", currentStep > step.id ? "text-emerald-500" : "text-gray-400")}
                        />
                    )}
                </React.Fragment>
            ))}
        </div>
    )


    // --- Render Step Content (Forms) ---
    // (This section needs to be carefully adapted to use Inertia's `data`, `setData`, and `errors`)
    const renderStepContent = () => {
        switch (currentStep) {
            case 1:
                return (
                    <div className="space-y-6 p-2">
                        {/* === Data Diri Section === */}
                        <h3 className="text-lg font-medium border-b pb-2 mb-4">Data Diri</h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="nama">
                                    Nama Lengkap <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="nama"
                                    placeholder="Masukkan nama lengkap"
                                    value={data.nama}
                                    onChange={(e) => setData("nama", e.target.value)}
                                    className={errors.nama ? "border-red-500" : ""}
                                />
                                {errors.nama && <p className="text-sm text-red-500">{errors.nama}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="nama_panggilan">
                                    Nama Panggilan <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="nama_panggilan"
                                    placeholder="Masukkan nama panggilan"
                                    value={data.nama_panggilan}
                                    onChange={(e) => setData("nama_panggilan", e.target.value)}
                                    className={errors.nama_panggilan ? "border-red-500" : ""}
                                />
                                {errors.nama_panggilan && <p className="text-sm text-red-500">{errors.nama_panggilan}</p>}
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label>
                                Jenis Kelamin <span className="text-red-500">*</span>
                            </Label>
                            <RadioGroup
                                value={data.jenis_kelamin}
                                onValueChange={(value) => setData("jenis_kelamin", value)}
                                className={cn("flex space-x-4", errors.jenis_kelamin ? "border border-red-500 rounded p-2" : "")}
                            >
                                {options.jenis_kelamin.map((option) => (
                                    <div key={option.value} className="flex items-center space-x-2">
                                        <RadioGroupItem value={option.value} id={`jk-${option.value}`} />
                                        <Label htmlFor={`jk-${option.value}`}>{option.label}</Label>
                                    </div>
                                ))}
                            </RadioGroup>
                            {errors.jenis_kelamin && <p className="text-sm text-red-500">{errors.jenis_kelamin}</p>}
                        </div>


                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="nomor_telepon">
                                    Nomor Telepon <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="nomor_telepon"
                                    placeholder="Contoh: 08123456789"
                                    value={data.nomor_telepon}
                                    onChange={(e) => setData("nomor_telepon", e.target.value)}
                                    className={errors.nomor_telepon ? "border-red-500" : ""}
                                />
                                {errors.nomor_telepon && <p className="text-sm text-red-500">{errors.nomor_telepon}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="email">
                                    Email <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="email"
                                    type="email"
                                    placeholder="Contoh: nama@email.com"
                                    value={data.email}
                                    onChange={(e) => setData("email", e.target.value)}
                                    className={errors.email ? "border-red-500" : ""}
                                />
                                {errors.email && <p className="text-sm text-red-500">{errors.email}</p>}
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="tempat_lahir">
                                    Tempat Lahir <span className="text-red-500">*</span>
                                </Label>
                                <Input
                                    id="tempat_lahir"
                                    placeholder="Masukkan tempat lahir"
                                    value={data.tempat_lahir}
                                    onChange={(e) => setData("tempat_lahir", e.target.value)}
                                    className={errors.tempat_lahir ? "border-red-500" : ""}
                                />
                                {errors.tempat_lahir && <p className="text-sm text-red-500">{errors.tempat_lahir}</p>}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="tanggal_lahir">
                                    Tanggal Lahir <span className="text-red-500">*</span>
                                </Label>
                                <Popover>
                                    <PopoverTrigger asChild>
                                        <Button
                                            variant="outline"
                                            className={cn(
                                                "w-full justify-start text-left font-normal",
                                                !data.tanggal_lahir && "text-muted-foreground",
                                                errors.tanggal_lahir ? "border-red-500" : ""
                                            )}
                                        >
                                            <CalendarIcon className="mr-2 h-4 w-4" />
                                            {data.tanggal_lahir ? (
                                                format(data.tanggal_lahir, "dd MMMM yyyy")
                                            ) : (
                                                <span>Pilih tanggal</span>
                                            )}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent className="w-auto p-0">
                                        <Calendar
                                            mode="single"
                                            selected={data.tanggal_lahir}
                                            onSelect={(date) => setData("tanggal_lahir", date)}
                                            captionLayout="dropdown-buttons" // Allow year/month selection
                                            fromYear={1950} // Example range
                                            toYear={new Date().getFullYear()}
                                            initialFocus
                                        />
                                    </PopoverContent>
                                </Popover>
                                {errors.tanggal_lahir && <p className="text-sm text-red-500">{errors.tanggal_lahir}</p>}
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="kewarganegaraan">
                                Kewarganegaraan <span className="text-red-500">*</span>
                            </Label>
                            <Select value={data.kewarganegaraan} onValueChange={(value) => setData("kewarganegaraan", value)}>
                                <SelectTrigger className={errors.kewarganegaraan ? "border-red-500" : ""}>
                                    <SelectValue placeholder="Pilih kewarganegaraan" />
                                </SelectTrigger>
                                <SelectContent>
                                    {options.negara.map((option) => (
                                        <SelectItem key={option.value} value={option.value}>
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.kewarganegaraan && <p className="text-sm text-red-500">{errors.kewarganegaraan}</p>}
                        </div>

                        {/* === Alamat Section === */}
                        <h3 className="text-lg font-medium border-b pb-2 mb-4 mt-6">Alamat</h3>

                        {/* --- Conditional Fields based on Kewarganegaraan --- */}
                        {kewarganegaraan === negaraIndonesia ? (
                            <>
                                {/* --- WNI Fields --- */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="nomor_induk_kependudukan">
                                            NIK <span className="text-red-500">*</span>
                                        </Label>
                                        <Input
                                            id="nomor_induk_kependudukan"
                                            placeholder="Masukkan 16 digit NIK"
                                            maxLength={16}
                                            value={data.nomor_induk_kependudukan}
                                            onChange={(e) => setData("nomor_induk_kependudukan", e.target.value)}
                                            className={errors.nomor_induk_kependudukan ? "border-red-500" : ""}
                                        />
                                        {errors.nomor_induk_kependudukan && (
                                            <p className="text-sm text-red-500">{errors.nomor_induk_kependudukan}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="nomor_kartu_keluarga">
                                            Nomor KK <span className="text-red-500">*</span>
                                        </Label>
                                        <Input
                                            id="nomor_kartu_keluarga"
                                            placeholder="Masukkan nomor kartu keluarga"
                                            value={data.nomor_kartu_keluarga}
                                            onChange={(e) => setData("nomor_kartu_keluarga", e.target.value)}
                                            className={errors.nomor_kartu_keluarga ? "border-red-500" : ""}
                                        />
                                        {errors.nomor_kartu_keluarga && (
                                            <p className="text-sm text-red-500">{errors.nomor_kartu_keluarga}</p>
                                        )}
                                    </div>
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="alamat">
                                        Alamat <span className="text-red-500">*</span>
                                    </Label>
                                    <Textarea
                                        id="alamat"
                                        placeholder="Masukkan alamat lengkap (Nama Jalan, Nomor Rumah, dll)"
                                        value={data.alamat}
                                        onChange={(e) => setData("alamat", e.target.value)}
                                        className={errors.alamat ? "border-red-500" : ""}
                                    />
                                    {errors.alamat && <p className="text-sm text-red-500">{errors.alamat}</p>}
                                </div>

                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="rt">RT <span className="text-red-500">*</span></Label>
                                        <Input id="rt" placeholder="RT" value={data.rt} onChange={e => setData('rt', e.target.value)} className={errors.rt ? "border-red-500" : ""} />
                                        {errors.rt && <p className="text-sm text-red-500">{errors.rt}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="rw">RW <span className="text-red-500">*</span></Label>
                                        <Input id="rw" placeholder="RW" value={data.rw} onChange={e => setData('rw', e.target.value)} className={errors.rw ? "border-red-500" : ""} />
                                        {errors.rw && <p className="text-sm text-red-500">{errors.rw}</p>}
                                    </div>
                                    <div className="space-y-2 col-span-2">
                                        <Label htmlFor="kode_pos">Kode Pos <span className="text-red-500">*</span></Label>
                                        <Input id="kode_pos" placeholder="Kode Pos" value={data.kode_pos} onChange={e => setData('kode_pos', e.target.value)} className={errors.kode_pos ? "border-red-500" : ""} />
                                        {errors.kode_pos && <p className="text-sm text-red-500">{errors.kode_pos}</p>}
                                    </div>
                                </div>


                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="provinsi_id">Provinsi <span className="text-red-500">*</span></Label>
                                        <Select value={data.provinsi_id} onValueChange={(value) => setData("provinsi_id", value)}>
                                            <SelectTrigger className={errors.provinsi_id ? "border-red-500" : ""}>
                                                <SelectValue placeholder="Pilih provinsi" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {provinsiOptions.map((option) => (
                                                    <SelectItem key={option.value} value={option.value}>
                                                        {option.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.provinsi_id && <p className="text-sm text-red-500">{errors.provinsi_id}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="kota_id">Kota/Kabupaten <span className="text-red-500">*</span></Label>
                                        <Select
                                            value={data.kota_id}
                                            onValueChange={(value) => setData("kota_id", value)}
                                            disabled={kotaOptions.length === 0}
                                        >
                                            <SelectTrigger className={errors.kota_id ? "border-red-500" : ""}>
                                                <SelectValue placeholder={kotaOptions.length === 0 ? "Pilih provinsi dulu" : "Pilih kota/kabupaten"} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {kotaOptions.map((option) => (
                                                    <SelectItem key={option.value} value={option.value}>
                                                        {option.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.kota_id && <p className="text-sm text-red-500">{errors.kota_id}</p>}
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="kecamatan_id">Kecamatan <span className="text-red-500">*</span></Label>
                                        <Select
                                            value={data.kecamatan_id}
                                            onValueChange={(value) => setData("kecamatan_id", value)}
                                            disabled={kecamatanOptions.length === 0}
                                        >
                                            <SelectTrigger className={errors.kecamatan_id ? "border-red-500" : ""}>
                                                <SelectValue placeholder={kecamatanOptions.length === 0 ? "Pilih kota dulu" : "Pilih kecamatan"} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {kecamatanOptions.map((option) => (
                                                    <SelectItem key={option.value} value={option.value}>
                                                        {option.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.kecamatan_id && <p className="text-sm text-red-500">{errors.kecamatan_id}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="kelurahan_id">Kelurahan/Desa <span className="text-red-500">*</span></Label>
                                        <Select
                                            value={data.kelurahan_id}
                                            onValueChange={(value) => setData("kelurahan_id", value)}
                                            disabled={kelurahanOptions.length === 0}
                                        >
                                            <SelectTrigger className={errors.kelurahan_id ? "border-red-500" : ""}>
                                                <SelectValue placeholder={kelurahanOptions.length === 0 ? "Pilih kecamatan dulu" : "Pilih kelurahan/desa"} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {kelurahanOptions.map((option) => (
                                                    <SelectItem key={option.value} value={option.value}>
                                                        {option.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.kelurahan_id && <p className="text-sm text-red-500">{errors.kelurahan_id}</p>}
                                    </div>
                                </div>


                            </>
                        ) : (
                            <>
                                {/* --- WNA Fields --- */}
                                <div className="space-y-2">
                                    <Label htmlFor="nomor_passport">
                                        Nomor Passport <span className="text-red-500">*</span>
                                    </Label>
                                    <Input
                                        id="nomor_passport"
                                        placeholder="Masukkan nomor passport"
                                        value={data.nomor_passport}
                                        onChange={(e) => setData("nomor_passport", e.target.value)}
                                        className={errors.nomor_passport ? "border-red-500" : ""}
                                    />
                                    {errors.nomor_passport && <p className="text-sm text-red-500">{errors.nomor_passport}</p>}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="alamat_non_indo">
                                        Address <span className="text-red-500">*</span>
                                    </Label>
                                    <Textarea
                                        id="alamat_non_indo"
                                        placeholder="Enter your full address"
                                        value={data.alamat_non_indo}
                                        onChange={(e) => setData("alamat_non_indo", e.target.value)}
                                        className={errors.alamat_non_indo ? "border-red-500" : ""}
                                    />
                                    {errors.alamat_non_indo && <p className="text-sm text-red-500">{errors.alamat_non_indo}</p>}
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="city">City <span className="text-red-500">*</span></Label>
                                        <Input id="city" placeholder="Enter your city" value={data.city} onChange={e => setData('city', e.target.value)} className={errors.city ? "border-red-500" : ""} />
                                        {errors.city && <p className="text-sm text-red-500">{errors.city}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="state_province">State/Province <span className="text-red-500">*</span></Label>
                                        <Input id="state_province" placeholder="Enter your state/province" value={data.state_province} onChange={e => setData('state_province', e.target.value)} className={errors.state_province ? "border-red-500" : ""} />
                                        {errors.state_province && <p className="text-sm text-red-500">{errors.state_province}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="kode_pos">Postal Code <span className="text-red-500">*</span></Label>
                                        <Input id="kode_pos" placeholder="Enter your postal code" value={data.kode_pos} onChange={e => setData('kode_pos', e.target.value)} className={errors.kode_pos ? "border-red-500" : ""} />
                                        {errors.kode_pos && <p className="text-sm text-red-500">{errors.kode_pos}</p>}
                                    </div>
                                </div>

                            </>
                        )}
                    </div>
                )

            case 2:
                return (
                    <div className="space-y-6 p-2">
                        {/* === Informasi Sambung Section === */}
                        <h3 className="text-lg font-medium border-b pb-2 mb-4">Informasi Sambung</h3>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="kelompok_sambung">Kelompok Sambung <span className="text-red-500">*</span></Label>
                                <Input id="kelompok_sambung" placeholder="Masukkan kelompok sambung" value={data.kelompok_sambung} onChange={e => setData('kelompok_sambung', e.target.value)} className={errors.kelompok_sambung ? "border-red-500" : ""} />
                                {errors.kelompok_sambung && <p className="text-sm text-red-500">{errors.kelompok_sambung}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="desa_sambung">Desa Sambung <span className="text-red-500">*</span></Label>
                                <Input id="desa_sambung" placeholder="Masukkan desa sambung" value={data.desa_sambung} onChange={e => setData('desa_sambung', e.target.value)} className={errors.desa_sambung ? "border-red-500" : ""} />
                                {errors.desa_sambung && <p className="text-sm text-red-500">{errors.desa_sambung}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="daerah_sambung_id">Daerah Sambung <span className="text-red-500">*</span></Label>
                                <Select value={data.daerah_sambung_id} onValueChange={(value) => setData("daerah_sambung_id", value)}>
                                    <SelectTrigger className={errors.daerah_sambung_id ? "border-red-500" : ""}>
                                        <SelectValue placeholder="Pilih daerah sambung" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {daerahSambungOptions.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.daerah_sambung_id && <p className="text-sm text-red-500">{errors.daerah_sambung_id}</p>}
                            </div>
                        </div>

                        <Separator />

                        {/* === Informasi Pondok & Pendidikan Section === */}
                        <h3 className="text-lg font-medium border-b pb-2 mb-4">Informasi Pondok & Pendidikan</h3>
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="flex items-center space-x-2">
                                <Switch id="status_mubaligh" checked={data.status_mubaligh} onCheckedChange={(checked) => setData("status_mubaligh", checked)} />
                                <Label htmlFor="status_mubaligh">Status Mubaligh</Label>
                                {errors.status_mubaligh && <p className="text-sm text-red-500">{errors.status_mubaligh}</p>}
                            </div>
                            <div className="flex items-center space-x-2">
                                <Switch id="pernah_mondok" checked={data.pernah_mondok || statusMubaligh} onCheckedChange={(checked) => setData("pernah_mondok", checked)} disabled={statusMubaligh}/>
                                <Label htmlFor="pernah_mondok">Pernah Mondok</Label>
                                {errors.pernah_mondok && <p className="text-sm text-red-500">{errors.pernah_mondok}</p>}
                            </div>
                        </div>

                        {/* Conditional Pondok Fields */}
                        {(pernahMondok || statusMubaligh) && (
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 pl-4 border-l-2 border-gray-300 mt-4 pt-4">
                                <div className="space-y-2">
                                    <Label htmlFor="nama_pondok_sebelumnya">Nama Pondok Sebelumnya <span className="text-red-500">*</span></Label>
                                    <Input id="nama_pondok_sebelumnya" placeholder="Masukkan nama pondok sebelumnya" value={data.nama_pondok_sebelumnya} onChange={e => setData('nama_pondok_sebelumnya', e.target.value)} className={errors.nama_pondok_sebelumnya ? "border-red-500" : ""} />
                                    {errors.nama_pondok_sebelumnya && <p className="text-sm text-red-500">{errors.nama_pondok_sebelumnya}</p>}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="lama_mondok_sebelumnya">Lama Mondok (tahun) <span className="text-red-500">*</span></Label>
                                    <Input id="lama_mondok_sebelumnya" type="number" min={1} placeholder="Lama mondok dalam tahun" value={data.lama_mondok_sebelumnya ?? ""} onChange={e => setData('lama_mondok_sebelumnya', e.target.value ? parseInt(e.target.value) : undefined)} className={errors.lama_mondok_sebelumnya ? "border-red-500" : ""} />
                                    {errors.lama_mondok_sebelumnya && <p className="text-sm text-red-500">{errors.lama_mondok_sebelumnya}</p>}
                                </div>
                            </div>
                        )}

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="pendidikan_terakhir">Pendidikan Terakhir <span className="text-red-500">*</span></Label>
                                <Select value={data.pendidikan_terakhir} onValueChange={(value) => setData("pendidikan_terakhir", value)}>
                                    <SelectTrigger className={errors.pendidikan_terakhir ? "border-red-500" : ""}>
                                        <SelectValue placeholder="Pilih pendidikan terakhir" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {options.pendidikan_terakhir.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.pendidikan_terakhir && <p className="text-sm text-red-500">{errors.pendidikan_terakhir}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="jurusan">Jurusan</Label>
                                <Input id="jurusan" placeholder="Masukkan jurusan (jika ada)" value={data.jurusan} onChange={e => setData('jurusan', e.target.value)} className={errors.jurusan ? "border-red-500" : ""} />
                                {errors.jurusan && <p className="text-sm text-red-500">{errors.jurusan}</p>}
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="universitas">Universitas <span className="text-red-500">*</span></Label>
                                <Input id="universitas" placeholder="Masukkan universitas" value={data.universitas} onChange={e => setData('universitas', e.target.value)} className={errors.universitas ? "border-red-500" : ""} />
                                {errors.universitas && <p className="text-sm text-red-500">{errors.universitas}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="program_studi">Program Studi <span className="text-red-500">*</span></Label>
                                <Input id="program_studi" placeholder="Masukkan program studi" value={data.program_studi} onChange={e => setData('program_studi', e.target.value)} className={errors.program_studi ? "border-red-500" : ""} />
                                {errors.program_studi && <p className="text-sm text-red-500">{errors.program_studi}</p>}
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="angkatan_kuliah">Angkatan Kuliah (Tahun) <span className="text-red-500">*</span></Label>
                                <Input id="angkatan_kuliah" type="number" min={1900} max={new Date().getFullYear()} placeholder="Contoh: 2021" value={data.angkatan_kuliah ?? ""} onChange={e => setData('angkatan_kuliah', e.target.value ? parseInt(e.target.value) : undefined)} className={errors.angkatan_kuliah ? "border-red-500" : ""} />
                                {errors.angkatan_kuliah && <p className="text-sm text-red-500">{errors.angkatan_kuliah}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="status_kuliah">Status Kuliah <span className="text-red-500">*</span></Label>
                                <Select value={data.status_kuliah} onValueChange={(value) => setData("status_kuliah", value)}>
                                    <SelectTrigger className={errors.status_kuliah ? "border-red-500" : ""}>
                                        <SelectValue placeholder="Pilih status kuliah" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {options.status_kuliah.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.status_kuliah && <p className="text-sm text-red-500">{errors.status_kuliah}</p>}
                            </div>
                        </div>
                    </div>
                )

            case 3:
                return (
                    <div className="space-y-6 p-2">
                        {/* === Informasi Tambahan Section === */}
                        <h3 className="text-lg font-medium border-b pb-2 mb-4">Informasi Tambahan</h3>
                        {/* ... (Render fields for Mulai Mengaji, Bahasa Makna, Bahasa Harian, Keahlian, Hobi, SIM, etc.) ... */}
                        {/* Example for Bahasa Harian (Tag Input) */}
                        <div className="space-y-2">
                            <Label htmlFor="bahasa_harian">Bahasa Harian (pisahkan dengan Enter) <span className="text-red-500">*</span></Label>
                            <div className="flex flex-wrap gap-2 mb-2 min-h-[40px] border rounded p-2">
                                {(data.bahasa_harian || []).map((bahasa, index) => (
                                    <Badge key={index} variant="secondary" className="px-3 py-1">
                                        {bahasa}
                                        <button type="button" className="ml-2 text-xs font-bold" onClick={() => removeTag("bahasa_harian", bahasa)}>×</button>
                                    </Badge>
                                ))}
                            </div>
                            <Input
                                id="bahasa_harian_input"
                                placeholder="Ketik bahasa lalu tekan Enter..."
                                onKeyDown={(e) => {
                                    if (e.key === "Enter") {
                                        e.preventDefault();
                                        handleTagInput("bahasa_harian", e.currentTarget.value);
                                        e.currentTarget.value = ""; // Clear input
                                    }
                                }}
                                className={errors.bahasa_harian ? "border-red-500" : ""}
                            />
                            {errors.bahasa_harian && <p className="text-sm text-red-500">{errors.bahasa_harian}</p>}
                        </div>
                        {/* ... (Similarly adapt Keahlian and Hobi) ... */}

                        {/* Example for SIM (Multi-select Badge) */}
                        <div className="space-y-2">
                            <Label>SIM (Pilih yang dimiliki)</Label>
                            <div className={cn("flex flex-wrap gap-2 border rounded p-2 min-h-[40px]", errors.sim ? "border-red-500" : "")}>
                                {options.jenis_sim.map((option) => (
                                    <Badge
                                        key={option.value}
                                        variant={(data.sim || []).includes(option.value) ? "default" : "outline"}
                                        className="px-3 py-1 cursor-pointer"
                                        onClick={() => handleSimChange(option.value)}
                                    >
                                        {option.label}
                                    </Badge>
                                ))}
                            </div>
                            {errors.sim && <p className="text-sm text-red-500">{errors.sim}</p>}
                        </div>


                        {/* ... (Adapt Tinggi Badan, Berat Badan, Riwayat Sakit, Alergi, Golongan Darah, Ukuran Baju, Status Pernikahan, Status Tinggal, Anak Nomor, Jumlah Saudara using Input, Select, Textarea as appropriate) ... */}

                        <Separator className="my-6" />

                        {/* === Informasi Orang Tua & Wali Section === */}
                        {/* --- Ayah --- */}
                        <h3 className="text-lg font-medium border-b pb-2 mb-4">Informasi Ayah</h3>
                        {/* ... (Render Nama Ayah, Status Ayah) ... */}
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label htmlFor="nama_ayah">Nama Ayah <span className="text-red-500">*</span></Label>
                                <Input id="nama_ayah" placeholder="Masukkan nama ayah" value={data.nama_ayah} onChange={e => setData('nama_ayah', e.target.value)} className={errors.nama_ayah ? "border-red-500" : ""} />
                                {errors.nama_ayah && <p className="text-sm text-red-500">{errors.nama_ayah}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label htmlFor="status_ayah">Status Ayah <span className="text-red-500">*</span></Label>
                                <Select value={data.status_ayah} onValueChange={(value) => setData("status_ayah", value)}>
                                    <SelectTrigger className={errors.status_ayah ? "border-red-500" : ""}>
                                        <SelectValue placeholder="Pilih status ayah" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {options.status_orang_tua.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.status_ayah && <p className="text-sm text-red-500">{errors.status_ayah}</p>}
                            </div>
                        </div>


                        {/* Conditional fields for Ayah */}
                        {statusAyah === statusOrangTuaHidup && (
                            <div className="pl-4 border-l-2 border-gray-300 mt-4 pt-4 space-y-4">
                                {/* ... (Render Nomor Telepon Ayah, Tempat/Tanggal Lahir Ayah, Pekerjaan, Dapukan, Alamat, Sambung Ayah) ... */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="nomor_telepon_ayah">Nomor Telepon Ayah <span className="text-red-500">*</span></Label>
                                        <Input id="nomor_telepon_ayah" placeholder="Masukkan nomor telepon ayah" value={data.nomor_telepon_ayah} onChange={e => setData('nomor_telepon_ayah', e.target.value)} className={errors.nomor_telepon_ayah ? "border-red-500" : ""} />
                                        {errors.nomor_telepon_ayah && <p className="text-sm text-red-500">{errors.nomor_telepon_ayah}</p>}
                                    </div>
                                    <div className="space-y-2">
                                        <Label htmlFor="tempat_lahir_ayah">Tempat Lahir Ayah <span className="text-red-500">*</span></Label>
                                        <Input id="tempat_lahir_ayah" placeholder="Masukkan tempat lahir ayah" value={data.tempat_lahir_ayah} onChange={e => setData('tempat_lahir_ayah', e.target.value)} className={errors.tempat_lahir_ayah ? "border-red-500" : ""} />
                                        {errors.tempat_lahir_ayah && <p className="text-sm text-red-500">{errors.tempat_lahir_ayah}</p>}
                                    </div>
                                </div>
                                {/* Add other Ayah fields similarly */}
                            </div>
                        )}

                        <Separator className="my-6" />

                        {/* --- Ibu --- */}
                        <h3 className="text-lg font-medium border-b pb-2 mb-4">Informasi Ibu</h3>
                        {/* ... (Render Nama Ibu, Status Ibu) ... */}
                        {/* Conditional fields for Ibu */}
                        {statusIbu === statusOrangTuaHidup && (
                            <div className="pl-4 border-l-2 border-gray-300 mt-4 pt-4 space-y-4">
                                {/* ... (Render fields for Ibu similar to Ayah) ... */}
                            </div>
                        )}

                        <Separator className="my-6" />

                        {/* --- Wali --- */}
                        <h3 className="text-lg font-medium border-b pb-2 mb-4">Informasi Wali</h3>
                        <div className="space-y-2">
                            <Label htmlFor="hubungan_wali">Hubungan Wali <span className="text-red-500">*</span></Label>
                            <Select value={data.hubungan_wali} onValueChange={(value) => setData("hubungan_wali", value)}>
                                <SelectTrigger className={errors.hubungan_wali ? "border-red-500" : ""}>
                                    <SelectValue placeholder="Pilih hubungan wali" />
                                </SelectTrigger>
                                <SelectContent>
                                    {options.hubungan_wali.map((option) => (
                                        <SelectItem key={option.value} value={option.value}>
                                            {option.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.hubungan_wali && <p className="text-sm text-red-500">{errors.hubungan_wali}</p>}
                        </div>

                        {/* Conditional fields for Wali */}
                        {hubunganWali !== hubunganWaliOrangTua && (
                            <div className="pl-4 border-l-2 border-gray-300 mt-4 pt-4 space-y-4">
                                {/* ... (Render Nama Wali, Nomor Telepon Wali, Pekerjaan, Dapukan, Alamat, Sambung Wali) ... */}
                            </div>
                        )}
                    </div>
                )

            case 4:
                return (
                    <div className="space-y-6 p-2">
                        <h3 className="text-lg font-medium border-b pb-2 mb-4">Upload Dokumen</h3>
                        <p className="text-sm text-muted-foreground">
                            Pastikan semua dokumen yang diperlukan telah diunggah. Format yang diterima: PDF, JPG, PNG. Ukuran
                            maksimal: 5MB.
                        </p>
                        {errors.dokumen && <p className="text-sm text-red-500 mb-4">{errors.dokumen}</p>} {/* General error for document array */}

                        <div className="space-y-6">
                            {requiredDokumen.map((dokumen, index) => {
                                // Find the corresponding file state in data.dokumen array
                                const fileState = data.dokumen[index]?.file;
                                // Construct the specific error key expected from Laravel validation
                                const fileErrorKey = `dokumen.${index}.file`;
                                const fileError = errors[fileErrorKey];

                                return (
                                    <div key={dokumen.id} className={cn("p-4 border rounded-lg space-y-3", fileError ? "border-red-500" : "")}>
                                        <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                                            <div>
                                                <h4 className="font-medium">
                                                    {dokumen.nama} <span className="text-red-500">*</span>
                                                </h4>
                                                <p className="text-sm text-muted-foreground">{dokumen.keterangan}</p>
                                            </div>
                                            {dokumen.template_url && (
                                                <Button variant="outline" size="sm" asChild>
                                                    <a href={dokumen.template_url} download target="_blank" rel="noopener noreferrer">
                                                        <Download className="mr-2 h-4 w-4" /> Download Template
                                                    </a>
                                                </Button>
                                            )}
                                        </div>

                                        <div className="grid gap-2">
                                            <div
                                                className={cn(
                                                    "border-2 border-dashed rounded-lg p-6 flex flex-col items-center justify-center cursor-pointer transition-colors",
                                                    fileState ? "border-emerald-500 bg-emerald-50" : "border-gray-300 hover:border-gray-400",
                                                    fileError ? "border-red-500" : ""
                                                )}
                                                onClick={() => document.getElementById(`file-upload-${dokumen.id}`)?.click()}
                                            >
                                                {fileState ? (
                                                    <div className="text-center">
                                                        {/* Display file info */}
                                                        <Check className="h-6 w-6 text-emerald-600 mx-auto mb-2" />
                                                        <p className="text-sm font-medium truncate max-w-[200px]">{fileState.name}</p>
                                                        <p className="text-xs text-muted-foreground mt-1">
                                                            {(fileState.size / 1024 / 1024).toFixed(2)} MB
                                                        </p>
                                                        <Button
                                                            variant="ghost" size="sm" className="mt-2 text-red-600 hover:text-red-700"
                                                            onClick={(e) => { e.stopPropagation(); handleFileChange(dokumen.id, null, index); }}>
                                                            Hapus File
                                                        </Button>
                                                    </div>
                                                ) : (
                                                    <> {/* Placeholder for upload */}
                                                        <Download className="h-6 w-6 text-gray-500 mb-2" />
                                                        <p className="text-sm font-medium">Klik untuk upload</p>
                                                        <p className="text-xs text-muted-foreground mt-1">PDF, JPG, PNG (Maks. 5MB)</p>
                                                    </>
                                                )}
                                                <input
                                                    id={`file-upload-${dokumen.id}`}
                                                    type="file"
                                                    accept=".pdf,.jpg,.jpeg,.png"
                                                    className="hidden"
                                                    onChange={(e) => handleFileChange(dokumen.id, e.target.files?.[0] || null, index)}
                                                />
                                            </div>
                                            {/* Display progress bar if available and file selected */}
                                            {progress && progress.percentage && fileState && (
                                                <div className="w-full bg-gray-200 rounded-full h-1.5 dark:bg-gray-700">
                                                    <div className="bg-blue-600 h-1.5 rounded-full" style={{ width: `${progress.percentage}%` }}></div>
                                                </div>
                                            )}
                                            {fileError && <p className="text-sm text-red-500">{fileError}</p>}
                                        </div>
                                    </div>
                                );
                            })}
                        </div>

                    </div>
                )

            default:
                return null
        }
    }

    // --- Final Render ---
    return (
        <>
            <Head title={`Pendaftaran Santri Baru ${tahunPendaftaran}`} />
            <Toaster /> {/* Add Toaster component here */}

            <div className="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
                <div className="max-w-4xl mx-auto">
                    {/* Header Section */}
                    <div className="text-center mb-8">
                        {/* Placeholder for Logo */}
                        <div className="flex justify-center mb-4">
                            <svg className="h-20 w-20 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.97l-2.387.975a2 2 0 01-1.022.547H6a2 2 0 00-2 2v3a2 2 0 002 2h12a2 2 0 002-2v-3a2 2 0 00-2-2h-1.572zM9 13a3 3 0 11-6 0 3 3 0 016 0zM21 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <h1 className="text-2xl md:text-3xl font-bold text-gray-900">Penerimaan Santri Baru {tahunPendaftaran}</h1>
                        <h2 className="text-xl font-medium text-emerald-600">PPM Roudlotul Jannah Surakarta</h2>
                        <p className="mt-2 text-gray-600">"Sarjana Mubaligh, Profesional, Religius"</p>
                    </div>

                    {/* Form Card */}
                    <Card className="shadow-lg overflow-hidden">
                        <CardHeader>
                            <CardTitle>Formulir Pendaftaran</CardTitle>
                            <CardDescription>Silakan lengkapi formulir pendaftaran berikut dengan data yang benar.</CardDescription>
                        </CardHeader>

                        <CardContent>
                            {renderStepIndicators()}

                            {/* --- Render current step's form content --- */}
                            <div className="py-4 px-1">
                                {renderStepContent()}
                            </div>

                        </CardContent>

                        {/* Footer with Navigation Buttons */}
                        <CardFooter className="flex justify-between bg-gray-50 p-4 border-t">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={handlePrevious}
                                disabled={currentStep === 1 || processing}
                            >
                                Sebelumnya
                            </Button>

                            {currentStep < STEPS.length ? (
                                <Button
                                    type="button"
                                    className="bg-emerald-600 hover:bg-emerald-700 text-white"
                                    onClick={handleNext}
                                    disabled={processing}
                                >
                                    Selanjutnya
                                </Button>
                            ) : (
                                <Button
                                    type="button" // Changed from submit as Inertia handles it
                                    className="bg-emerald-600 hover:bg-emerald-700 text-white"
                                    onClick={handleSubmit}
                                    disabled={processing}
                                >
                                    {processing ? (
                                        <>
                                            <Loader2 className="mr-2 h-4 w-4 animate-spin" /> Mengirim...
                                        </>
                                    ) : (
                                        "Kirim Pendaftaran"
                                    )}
                                </Button>
                            )}
                        </CardFooter>
                    </Card>
                </div>
            </div>
        </>
    )
}

export default PendaftaranCreate
