import React, { useEffect, useState } from "react"
import { motion } from "framer-motion"
import confetti from "canvas-confetti"
import { ExternalLink } from "lucide-react"
import {Link} from "@inertiajs/react";

export default function FinishPage({ link_grup }) {
    const [isClient, setIsClient] = useState(false)

    useEffect(() => {
        setIsClient(true)

        // Trigger confetti effect
        const duration = 3 * 1000
        const animationEnd = Date.now() + duration
        const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 }

        function randomInRange(min: number, max: number) {
            return Math.random() * (max - min) + min
        }

        const interval: NodeJS.Timeout = setInterval(() => {
            const timeLeft = animationEnd - Date.now()

            if (timeLeft <= 0) {
                return clearInterval(interval)
            }

            const particleCount = 50 * (timeLeft / duration)

            // Since particles fall down, start a bit higher than random
            confetti(
                Object.assign({}, defaults, {
                    particleCount,
                    origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 },
                }),
            )
            confetti(
                Object.assign({}, defaults, {
                    particleCount,
                    origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 },
                }),
            )
        }, 250)

        return () => clearInterval(interval)
    }, [])

    return (
        <div className="min-h-screen bg-gradient-to-br from-teal-50 to-white flex flex-col items-center justify-center p-4">
            <div className="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 text-center">
                <motion.div
                    initial={{scale: 0.8, opacity: 0}}
                    animate={{scale: 1, opacity: 1}}
                    transition={{duration: 0.5}}
                    className="flex flex-col items-center"
                >
                    {/* Logo */}
                    <img
                        src="/logo.png"
                        alt="PPM Roudlotul Jannah Logo Placeholder"
                        className="relative inline-flex h-16 w-16 z-10 object-cover drop-shadow"
                    />

                    {/* Success message */}
                    <h1 className="text-3xl font-bold text-gray-900 mt-4 mb-2">Pendaftaran Berhasil!</h1>
                    <div className="w-16 h-1 bg-teal-500 mx-auto rounded-full mb-6"></div>

                    <h2 className="text-xl font-semibold mb-2">PPM Roudlotul Jannah Surakarta</h2>
                    <p className="mb-16 text-md italic font-medium text-zinc-700 text-center">
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

                    <div className="bg-green-50 border border-green-200 rounded-lg p-4 mb-6 text-left">
                        {
                            link_grup ?
                                <p className="text-green-800 text-justify">
                                    Selamat! Pendaftaran Anda telah berhasil diproses. Silakan bergabung dengan grup
                                    WhatsApp
                                    untuk informasi
                                    selanjutnya.
                                </p>
                                :
                                <p className="text-green-800 text-justify">
                                    Selamat! Pendaftaran Anda telah berhasil diproses. Silakan menunggu untuk informasi selanjutnya.
                                </p>
                        }

                    </div>

                    {
                        link_grup ?
                            <a
                                href={link_grup}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-6 rounded-lg flex items-center justify-center gap-2 transition-colors"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="20"
                                    height="20"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                    className="shrink-0"
                                >
                                    <path
                                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                Gabung Grup WhatsApp
                                <ExternalLink className="h-4 w-4"/>
                            </a>
                        :
                        <></>
                    }



                    <Link
                        href="/"
                        className="mt-4 text-teal-600 hover:text-teal-800 font-medium inline-flex items-center gap-1"
                    >
                        Kembali ke Halaman Pendaftaran
                    </Link>
                </motion.div>
            </div>
        </div>
    )
}
