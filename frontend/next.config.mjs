/** @type {import('next').NextConfig} */
const nextConfig = {
  output: 'standalone',
  images: {
    // Logo 来自各工具官网/favicon 服务，域名不可枚举，直接使用 <img>
    unoptimized: true,
  },
};

export default nextConfig;
