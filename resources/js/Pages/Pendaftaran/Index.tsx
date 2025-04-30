import type React from "react"
import { useState, useEffect } from "react"
import {
    ArrowRight,
    FileText,
    ClipboardCheck,
    Phone,
    UserCheck,
    HeartHandshake,
    ChevronDown,
    ChevronUp,
    ExternalLink, Download,
} from "lucide-react"
import { motion } from "framer-motion"

// Types
interface GelombangPendaftaran {
    id: number
    nomor_gelombang: number
    awal_pendaftaran: string
    akhir_pendaftaran: string
    timeline?: Array<{
        nama_kegiatan: string
        tanggal: string
    }>
}

interface DokumenPendaftaran {
    nama: string
    keterangan: string
    url: string
}

interface IndikatorPenilaian {
    nama: string
}

interface KontakPanitia {
    nama: string
    jabatan: string
    nomor_telepon: string
}

interface Pendaftaran {
    id: number
    tahun: number
    gelombang_pendaftaran: GelombangPendaftaran[]
    dokumen_pendaftaran: DokumenPendaftaran[]
    indikator_penilaian: IndikatorPenilaian[]
    kontak_panitia: KontakPanitia[]
    kontak_pengurus?: KontakPanitia[]
}

interface Props {
    pendaftaran: Pendaftaran | null
    statusPendaftaran:
        | "belum_dibuka"
        | "menunggu_pembukaan"
        | "sedang_dibuka"
        | "menunggu_gelombang_berikutnya"
        | "ditutup"
    pesanStatus: string
    tombolAksiTeks: string | null
    gelombangAktifId: number | null
    kampusSekitar: Record<string, string[]>
    jargon: string
    mengapaMondok: string
    kontakPanitia: KontakPanitia[]
}

// Helper function to format dates
const formatDate = (dateString: string): string => {
    const options: Intl.DateTimeFormatOptions = {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
    }
    return new Date(dateString).toLocaleDateString("id-ID", options)
}

// Text animation component inspired by Aceternity UI
const TextGenerateEffect = ({ text }: { text: string }) => {
    const [displayedText, setDisplayedText] = useState("")

    useEffect(() => {
        let i = 0
        const interval = setInterval(() => {
            if (i < text.length) {
                setDisplayedText(text.substring(0, i + 1))
                i++
            } else {
                clearInterval(interval)
            }
        }, 30)

        return () => clearInterval(interval)
    }, [text])

    return <span>{displayedText}</span>
}

// Tracing beam component inspired by Aceternity UI
const TracingBeam = ({ children }: { children: React.ReactNode }) => {
    return (
        <div className="relative">
            <div className="absolute left-8 top-0 bottom-0 w-1 bg-gradient-to-b from-green-500 to-green-100 opacity-20 rounded-full"></div>
            <div className="relative ml-12">{children}</div>
        </div>
    )
}

// Main component
const PendaftaranIndex: React.FC<Props> = ({
                                               pendaftaran,
                                               statusPendaftaran,
                                               pesanStatus,
                                               tombolAksiTeks,
                                               gelombangAktifId,
                                               kampusSekitar,
                                               jargon,
                                               mengapaMondok,
                                               kontakPanitia,
                                           }) => {
    const [activeTab, setActiveTab] = useState<string>(Object.keys(kampusSekitar)[0])
    const [expandedAccordion, setExpandedAccordion] = useState<number | null>(null)

    // Toggle accordion
    const toggleAccordion = (index: number) => {
        setExpandedAccordion(expandedAccordion === index ? null : index)
    }

    // Scroll to section
    const scrollToSection = (id: string) => {
        const element = document.getElementById(id)
        if (element) {
            element.scrollIntoView({ behavior: "smooth" })
        }
    }

    return (
        <div className="font-sans bg-white">
            {/* Hero Section */}
            <section className="relative min-h-screen flex items-center justify-center overflow-hidden bg-gradient-to-br from-green-50 to-white">
                <div className="absolute inset-0 z-0">
                    <div className="absolute inset-0 bg-[url('/placeholder.svg?height=1080&width=1920')] bg-cover bg-center opacity-10"></div>
                    <div className="absolute inset-0 bg-gradient-to-b from-green-500/10 to-white/80"></div>
                </div>

                <div className="container mx-auto px-4 py-16 z-10 text-center">
                    <div className="flex justify-center mb-8">
                        <div className="bg-white/90 p-4 rounded-full shadow-lg">
                            <div
                                className="w-24 h-24 flex items-center justify-center text-white rounded-full">
                                <img
                                    src="/logo.png"
                                    alt="PPM Roudlotul Jannah Logo Placeholder"
                                    className="relative inline-flex h-24 w-24 z-10 object-cover drop-shadow"
                                />
                            </div>
                        </div>
                    </div>

                    <motion.div
                        initial={{opacity: 0, y: 20}}
                        animate={{opacity: 1, y: 0}}
                        transition={{duration: 0.8}}
                        className="mb-6"
                    >
                        <h1 className="text-4xl md:text-5xl font-bold text-green-600 mb-2  text-center">
                            PPM Roudlotul Jannah Surakarta
                        </h1>
                    </motion.div>

                    <motion.div
                        initial={{opacity: 0}}
                        animate={{opacity: 1}}
                        transition={{delay: 0.4, duration: 0.8}}
                        className="flex items-center justify-center gap-3 mb-4"
                    >
                        <p className="mb-16 text-lg md:text-xl italic font-medium text-zinc-700 text-center">
                            Sarjana yang Mubaligh,
                            <span className="text-green-400 mx-1 relative inline-block stroke-current">
                                Mubaligh yang Sarjana
                                <svg className="absolute -bottom-0.5 w-full max-h-1.5" viewBox="0 0 55 5"
                                     xmlns="http://www.w3.org/2000/svg"
                                     preserveAspectRatio="none">
                                    <path d="M0.652466 4.00002C15.8925 2.66668 48.0351 0.400018 54.6853 2.00002"
                                          stroke-width="2"></path>
                                </svg>
                            </span>
                        </p>
                    </motion.div>

                    <motion.div
                        initial={{opacity: 0, y: 20}}
                        animate={{opacity: 1, y: 0}}
                        transition={{delay: 0.8, duration: 0.8}}
                        className="max-w-3xl mx-auto mb-12 p-6 bg-white/80 backdrop-blur-sm rounded-xl shadow-lg"
                    >
                        <p className="text-lg text-gray-700 mb-4">{pesanStatus}</p>

                        {tombolAksiTeks && (
                            <button
                                onClick={() => {
                                    if (statusPendaftaran === "sedang_dibuka" && gelombangAktifId) {
                                        window.location.href = `/pendaftaran/create`
                                    } else if (
                                        statusPendaftaran === "menunggu_pembukaan" ||
                                        statusPendaftaran === "menunggu_gelombang_berikutnya"
                                    ) {
                                        scrollToSection("timeline")
                                    } else if (statusPendaftaran === "ditutup") {
                                        scrollToSection("contact")
                                    }
                                }}
                                className={`
                                  inline-flex items-center gap-2 px-6 py-3 rounded-lg font-medium transition-all
                                  ${
                                        statusPendaftaran === "sedang_dibuka"
                                            ? "bg-green-600 hover:bg-green-700 text-white shadow-lg hover:shadow-xl"
                                            : "bg-white border border-green-600 text-green-600 hover:bg-green-50"
                                    }
                                  `}
                            >
                                {tombolAksiTeks}
                                <ArrowRight className="h-5 w-5" />
                            </button>
                        )}
                    </motion.div>

                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        transition={{ delay: 1.2, duration: 0.8 }}
                        className="absolute bottom-10 left-0 right-0 flex justify-center"
                    >
                        <button
                            onClick={() => scrollToSection("why-choose")}
                            className="text-gray-500 hover:text-green-600 transition-colors"
                        >
                            <ChevronDown className="h-8 w-8 animate-bounce" />
                        </button>
                    </motion.div>
                </div>
            </section>

            {/* Why Choose Section */}
            <section id="why-choose" className="py-20 bg-white">
                <div className="container mx-auto px-4">
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        viewport={{ once: true }}
                        className="text-center mb-12"
                    >
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                            Mengapa bergabung dengan PPM?
                        </h2>
                        <div className="w-24 h-1 bg-green-500 mx-auto rounded-full"></div>
                    </motion.div>

                    <div className="max-w-4xl mx-auto">
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.8, delay: 0.2 }}
                            viewport={{ once: true }}
                            className="bg-gradient-to-br from-green-50 to-white p-8 rounded-2xl shadow-lg"
                        >
                            <div className="flex flex-col md:flex-row gap-8">
                                <div className="flex-1">
                                    <div className="flex items-start gap-4 mb-6">
                                        <div className="bg-green-100 p-3 rounded-full">
                                            <BookOpenCheck className="h-6 w-6 text-green-600" />
                                        </div>
                                        <div>
                                            <h3 className="text-xl font-semibold text-gray-900 mb-2">Keseimbangan Akademik & Spiritual</h3>
                                            <p className="text-gray-700">
                                                Mengejar gelar akademik sambil memperdalam pengetahuan dan praktik agama.
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex items-start gap-4 mb-6">
                                        <div className="bg-green-100 p-3 rounded-full">
                                            <UserCheck className="h-6 w-6 text-green-600" />
                                        </div>
                                        <div>
                                            <h3 className="text-xl font-semibold text-gray-900 mb-2">Pembentukan Karakter</h3>
                                            <p className="text-gray-700">
                                                Mengembangkan karakter mulia dan nilai-nilai etika dalam lingkungan Islami yang mendukung.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div className="flex-1">
                                    <div className="flex items-start gap-4 mb-6">
                                        <div className="bg-green-100 p-3 rounded-full">
                                            <Users className="h-6 w-6 text-green-600" />
                                        </div>
                                        <div>
                                            <h3 className="text-xl font-semibold text-gray-900 mb-2">Persaudaraan yang Kuat</h3>
                                            <p className="text-gray-700">
                                                Membangun jaringan teman sejawat yang memiliki nilai dan aspirasi yang sama.
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex items-start gap-4">
                                        <div className="bg-green-100 p-3 rounded-full">
                                            <HeartHandshake className="h-6 w-6 text-green-600" />
                                        </div>
                                        <div>
                                            <h3 className="text-xl font-semibold text-gray-900 mb-2">Pengabdian Masyarakat</h3>
                                            <p className="text-gray-700">
                                                Belajar berkontribusi secara bermakna kepada masyarakat dan menjadi kehadiran yang bermanfaat.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="mt-8 p-4 bg-white rounded-lg border border-green-100">
                                <p className="text-gray-700">{mengapaMondok}</p>
                            </div>
                        </motion.div>
                    </div>
                </div>
            </section>

            {/* Timeline Section */}
            <section id="timeline" className="py-20 bg-gray-50">
                <div className="container mx-auto px-4">
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        viewport={{ once: true }}
                        className="text-center mb-12"
                    >
                        <div className="flex items-center justify-center gap-3 mb-4">
                            <h2 className="text-3xl md:text-4xl font-bold text-gray-900">
                               Timeline Pendaftaran {pendaftaran?.tahun || new Date().getFullYear()}
                            </h2>
                        </div>
                        <div className="w-24 h-1 bg-green-500 mx-auto rounded-full"></div>
                    </motion.div>

                    {pendaftaran && pendaftaran.gelombang_pendaftaran && pendaftaran.gelombang_pendaftaran.length > 0 ? (
                        <div className="w-11/12 md:w-2/3 mx-auto">
                            <TracingBeam>
                                {pendaftaran.gelombang_pendaftaran.map((gelombang, index) => (
                                    <motion.div
                                        key={gelombang.id}
                                        initial={{ opacity: 0, y: 20 }}
                                        whileInView={{ opacity: 1, y: 0 }}
                                        transition={{ duration: 0.5, delay: index * 0.1 }}
                                        viewport={{ once: true }}
                                        className={`mb-8 ${gelombangAktifId === gelombang.id ? "relative" : ""}`}
                                    >
                                        {gelombangAktifId === gelombang.id && (
                                            <div className="absolute -left-16 top-1/2 transform -translate-y-1/2">
                                                <div className="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                                    <div className="w-3 h-3 bg-white rounded-full"></div>
                                                </div>
                                            </div>
                                        )}

                                        <div
                                            className={`
                                                p-6 rounded-xl shadow-md transition-all cursor-pointer
                                                ${
                                                gelombangAktifId === gelombang.id
                                                    ? "bg-green-50 border-2 border-green-500"
                                                    : "bg-white hover:bg-gray-50"
                                            }
                                            `}
                                            onClick={() => toggleAccordion(index)}
                                        >
                                            <div className="flex justify-between items-center">
                                                <div>
                                                    <h3 className="text-xl font-semibold text-gray-900">
                                                        Gelombang {gelombang.nomor_gelombang}
                                                        {gelombangAktifId === gelombang.id && (
                                                            <span className="ml-2 inline-block px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                                Aktif
                                                            </span>
                                                        )}
                                                    </h3>
                                                    <p className="text-gray-600 overflow-wrap">
                                                        {formatDate(gelombang.awal_pendaftaran)} - {formatDate(gelombang.akhir_pendaftaran)}
                                                    </p>
                                                </div>
                                                {expandedAccordion === index ? (
                                                    <ChevronUp className="h-5 w-5 text-gray-500" />
                                                ) : (
                                                    <ChevronDown className="h-5 w-5 text-gray-500" />
                                                )}
                                            </div>

                                            {expandedAccordion === index && gelombang.timeline && (
                                                <div className="mt-4 pt-4 border-t border-gray-100">
                                                    <h4 className="font-medium text-gray-900 mb-2">Timeline Detail:</h4>
                                                    <ul className="space-y-2">
                                                        {gelombang.timeline.map((item, i) => (
                                                            <li key={i} className="flex items-start gap-2">
                                                                <div className="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                                                <div>
                                                                    <span className="font-medium">{item.nama_kegiatan}:</span>{" "}
                                                                    <span className="text-gray-600">{formatDate(item.tanggal)}</span>
                                                                </div>
                                                            </li>
                                                        ))}
                                                    </ul>
                                                </div>
                                            )}
                                        </div>
                                    </motion.div>
                                ))}
                            </TracingBeam>
                        </div>
                    ) : (
                        <div className="text-center p-8 bg-white rounded-lg shadow-md max-w-2xl mx-auto">
                            <p className="text-gray-700 text-lg">Jadwal pendaftaran akan diumumkan segera.</p>
                        </div>
                    )}
                </div>
            </section>

            {/* Requirements & Assessment Section */}
            <section className="py-20 bg-white">
                <div className="container mx-auto px-4">
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        viewport={{ once: true }}
                        className="text-center mb-12"
                    >
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Persyaratan & Penilaian Pendaftaran</h2>
                        <div className="w-24 h-1 bg-green-500 mx-auto rounded-full"></div>
                    </motion.div>

                    <div className="grid md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                        <motion.div
                            initial={{ opacity: 0, x: -20 }}
                            whileInView={{ opacity: 1, x: 0 }}
                            transition={{ duration: 0.8 }}
                            viewport={{ once: true }}
                            className="bg-white p-6 rounded-xl shadow-md border border-gray-100"
                        >
                            <div className="flex items-center gap-3 mb-6">
                                <div className="bg-green-100 p-3 rounded-full">
                                    <FileText className="h-6 w-6 text-green-600" />
                                </div>
                                <h3 className="text-xl font-semibold text-gray-900">Dokumen yang Diperlukan</h3>
                            </div>

                            {pendaftaran && pendaftaran.dokumen_pendaftaran && pendaftaran.dokumen_pendaftaran.length > 0 ? (
                                <ul className="space-y-5">
                                    {pendaftaran.dokumen_pendaftaran.map((doc, index) => (
                                        <li key={index} className="flex items-start gap-2">
                                            <div className="min-w-4 w-4 h-4 bg-green-500 rounded-full mt-1.5"></div>
                                            <div>
                                                <span className="font-medium">{doc.nama}</span>
                                                {doc.keterangan && <p className="text-sm text-gray-600 mt-1">{doc.keterangan}</p>}
                                                {/* Download Button (only shows if doc.url exists) */}
                                                {doc.url && (
                                                    <div className="mt-4">
                                                        <a
                                                            href={doc.url}
                                                            download
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="inline-flex items-center gap-1.5 px-5 py-2 bg-green-600 text-white font-medium rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150"
                                                        >
                                                            <Download className="h-4 w-4" />
                                                            <span>Template</span>
                                                        </a>
                                                    </div>
                                                )}
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <p className="text-gray-700">Informasi dokumen akan tersedia segera.</p>
                            )}
                        </motion.div>

                        <motion.div
                            initial={{ opacity: 0, x: 20 }}
                            whileInView={{ opacity: 1, x: 0 }}
                            transition={{ duration: 0.8 }}
                            viewport={{ once: true }}
                            className="bg-white p-6 rounded-xl shadow-md border border-gray-100"
                        >
                            <div className="flex items-center gap-3 mb-6">
                                <div className="bg-green-100 p-3 rounded-full">
                                    <ClipboardCheck className="h-6 w-6 text-green-600" />
                                </div>
                                <h3 className="text-xl font-semibold text-gray-900">Indikator Penilaian</h3>
                            </div>

                            {pendaftaran && pendaftaran.indikator_penilaian && pendaftaran.indikator_penilaian.length > 0 ? (
                                <div>
                                    <p className="text-gray-700 mb-4">
                                        Berikut adalah indikator umum yang digunakan dalam proses penilaian:
                                    </p>
                                    <ul className="space-y-5">
                                        {pendaftaran.indikator_penilaian.map((indikator, index) => (
                                            <li key={index} className="flex items-start gap-2">
                                                <div className="min-w-4 w-4 h-4 bg-green-500 rounded-full mt-1.5"></div>
                                                <span>{indikator.nama}</span>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            ) : (
                                <p className="text-gray-700">Detail penilaian akan tersedia segera.</p>
                            )}
                        </motion.div>
                    </div>
                </div>
            </section>

            {/* Nearby Campuses Section */}
            <section className="py-20 bg-gray-50">
                <div className="container mx-auto px-4">
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        viewport={{ once: true }}
                        className="text-center mb-12"
                    >
                        <div className="flex items-center justify-center gap-3 mb-4">
                            <h2 className="text-3xl md:text-4xl font-bold text-gray-900">
                                Perguruan Tinggi Terdekat
                            </h2>
                        </div>
                        <div className="w-24 h-1 bg-green-500 mx-auto rounded-full"></div>
                        <p className="text-gray-600 mt-4 max-w-2xl mx-auto">
                            Lokasi strategis kami memberikan akses mudah ke berbagai institusi pendidikan.
                        </p>
                    </motion.div>

                    <div className="max-w-5xl mx-auto">
                        <div className="bg-white rounded-xl shadow-md overflow-hidden">
                            <div className="flex flex-wrap border-b">
                                {Object.keys(kampusSekitar).map((area) => (
                                    <button
                                        key={area}
                                        className={`px-4 py-3 font-medium text-sm transition-colors ${
                                            activeTab === area
                                                ? "bg-green-50 text-green-700 border-b-2 border-green-500"
                                                : "text-gray-600 hover:bg-gray-50"
                                        }`}
                                        onClick={() => setActiveTab(area)}
                                    >
                                        {area}
                                    </button>
                                ))}
                            </div>

                            <div className="p-6">
                                {Object.entries(kampusSekitar).map(([area, institutions]) => (
                                    <div key={area} className={activeTab === area ? "block" : "hidden"}>
                                        <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ duration: 0.5 }}>
                                            <h3 className="text-xl font-semibold text-gray-900 mb-4">{area}</h3>
                                            <div className="grid md:grid-cols-2 gap-x-8 gap-y-2">
                                                {institutions.map((institution, index) => (
                                                    <div key={index} className="flex items-start gap-2 py-1">
                                                        <div className="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                                        <span className="text-gray-700">{institution}</span>
                                                    </div>
                                                ))}
                                            </div>
                                        </motion.div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Contact Section */}
            <section id="contact" className="py-20 bg-green-900 text-white">
                <div className="container mx-auto px-4">
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                        viewport={{ once: true }}
                        className="text-center mb-12"
                    >
                        <h2 className="text-3xl md:text-4xl font-bold mb-4">Informasi Kontak</h2>
                        <div className="w-24 h-1 bg-green-300 mx-auto rounded-full"></div>
                    </motion.div>

                    <div className="max-w-4xl mx-auto">
                        {kontakPanitia && kontakPanitia.length > 0 ? (
                            <div className="grid md:grid-cols-2 gap-6">
                                {kontakPanitia.map((kontak, index) => (
                                    <motion.div
                                        key={index}
                                        initial={{ opacity: 0, y: 20 }}
                                        whileInView={{ opacity: 1, y: 0 }}
                                        transition={{ duration: 0.5, delay: index * 0.1 }}
                                        viewport={{ once: true }}
                                        className="bg-green-800/50 p-5 rounded-lg backdrop-blur-sm"
                                    >
                                        <div className="flex items-start gap-4">
                                            <div className="bg-green-700 p-3 rounded-full">
                                                <Phone className="h-5 w-5 text-green-100" />
                                            </div>
                                            <div>
                                                <h3 className="font-semibold text-lg text-green-50">{kontak.nama}</h3>
                                                <p className="text-green-200 text-sm mb-2">{kontak.jabatan}</p>
                                                <a
                                                    href={`https://wa.me/${kontak.nomor_telepon.replace(/\D/g, "")}`}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    className="inline-flex items-center gap-1 text-green-100 hover:text-white transition-colors"
                                                >
                                                    {kontak.nomor_telepon}
                                                    <ExternalLink className="h-3 w-3" />
                                                </a>
                                            </div>
                                        </div>
                                    </motion.div>
                                ))}
                            </div>
                        ) : (
                            <div className="text-center p-8 bg-green-800/50 rounded-lg">
                                <p className="text-green-100">Informasi kontak akan tersedia segera.</p>
                            </div>
                        )}

                        <div className="mt-12 text-center">
                            <p className="text-green-200">
                                PPM Roudlotul Jannah Surakarta
                                <br />
                                Jl. Porong, Pucangsawit, Kec. Jebres, Kota Surakarta, Jawa Tengah 57125
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            {/* Footer */}
            <footer className="bg-green-950 text-green-300 py-8">
                <div className="container mx-auto px-4 text-center">
                    <div className="flex justify-center mb-4">
                        <img
                            src="/logo.png"
                            alt="PPM Roudlotul Jannah Logo Placeholder"
                            className="relative inline-flex h-16 w-16 z-10 object-cover drop-shadow"
                        />
                    </div>
                    <p>© {new Date().getFullYear()} PPM Roudlotul Jannah Surakarta. Hak Cipta Dilindungi.</p>
                </div>
            </footer>
        </div>
    )
}

// Missing components
const BookOpenCheck = ({ className }: { className?: string }) => (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
        className={className}
    >
        <path d="M8 3H2v15h7c1.7 0 3 1.3 3 3V7c0-2.2-1.8-4-4-4Z" />
        <path d="m16 12 2 2 4-4" />
        <path d="M22 6V3h-6c-2.2 0-4 1.8-4 4v14c0-1.7 1.3-3 3-3h7v-2.3" />
    </svg>
)

const Users = ({ className }: { className?: string }) => (
    <svg
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth="2"
        strokeLinecap="round"
        strokeLinejoin="round"
        className={className}
    >
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
        <circle cx="9" cy="7" r="4" />
        <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
    </svg>
)

export default PendaftaranIndex
