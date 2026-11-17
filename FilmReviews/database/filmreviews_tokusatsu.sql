-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 17, 2026 lúc 10:42 AM
-- Phiên bản máy phục vụ: 10.11.16-MariaDB
-- Phiên bản PHP: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `filmreviews_tokusatsu`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_role` int(11) NOT NULL DEFAULT 0,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `user_name`, `user_role`, `action`, `details`, `ip_address`, `created_at`) VALUES
(329, 1, 'Admin', 1, 'USER_LOGIN', 'User \'Admin\' (admin@tokusatsu.com) logged in successfully', '::1', '2026-11-17 13:39:56'),
(330, 105, 'test', 0, 'USER_REGISTER', 'New user registered account: \'test\' (test@tokusatsu.com)', '::1', '2026-11-17 15:06:37'),
(331, 105, 'test', 0, 'USER_LOGIN', 'User \'test\' (test@tokusatsu.com) logged in successfully', '::1', '2026-11-17 15:06:50'),
(332, 105, 'test', 0, 'SUBMIT_REVIEW', 'Reviewer posted a 4-star review (ID: #64) on film ID #108', '::1', '2026-11-17 15:09:40'),
(333, 105, 'test', 0, 'EDIT_REVIEW', 'Reviewer updated review ID #64 on film ID #108', '::1', '2026-11-17 15:11:54'),
(334, 105, 'test', 0, 'SUBMIT_REVIEW', 'Reviewer posted a 4-star review (ID: #65) on film ID #108', '::1', '2026-11-17 15:14:12'),
(335, 105, 'test', 0, 'DELETE_REVIEW', 'Reviewer deleted own review ID #65 on film ID #108', '::1', '2026-11-17 15:14:36'),
(336, 105, 'test', 0, 'SUBMIT_REVIEW', 'Reviewer posted a 4-star review (ID: #66) on film ID #108', '::1', '2026-11-17 15:14:52'),
(337, 1, 'Admin', 1, 'DELETE_REVIEW', 'Administrator deleted review ID #66 from Manage Reviews panel', '::1', '2026-11-17 15:15:06'),
(338, 106, 'Amory', 0, 'USER_REGISTER', 'New user registered account: \'Amory\' (amory@tokusatsu.com)', '::1', '2026-11-17 15:19:08'),
(340, 106, 'Amory', 0, 'USER_LOGIN', 'User \'Amory\' (amory@tokusatsu.com) logged in successfully', '::1', '2026-11-17 15:20:44'),
(342, 106, 'Amory', 0, 'SUBMIT_REVIEW', 'Reviewer posted a 4-star review (ID: #67) on film ID #108', '::1', '2026-11-17 15:22:31'),
(343, 106, 'Amory', 0, 'EDIT_REVIEW', 'Reviewer updated review ID #67 on film ID #108', '::1', '2026-11-17 15:33:55'),
(344, 106, 'Amory', 0, 'SUBMIT_FILM', 'Member submitted new film \'testpostfilm\' (ID: #151) for approval', '::1', '2026-11-17 15:37:17'),
(345, 106, 'Amory', 0, 'SUBMIT_FILM', 'Member submitted new film \'Ultraman Decker D\' (ID: #152) for approval', '::1', '2026-11-17 15:43:30'),
(348, 1, 'Admin', 1, 'REJECT_FILM', 'Rejected and deleted pending film ID: 154', '::1', '2026-11-17 15:44:22'),
(349, 1, 'Admin', 1, 'REJECT_FILM', 'Rejected and deleted pending film ID: 152', '::1', '2026-11-17 15:44:26'),
(350, 1, 'Admin', 1, 'APPROVE_FILM', 'Approved and published film ID: 153', '::1', '2026-11-17 15:47:54'),
(353, 107, 'subadmin', 2, 'USER_LOGIN', 'User \'subadmin\' (Sub_admin@tokusatsu.com) logged in successfully', '::1', '2026-11-17 16:04:07'),
(356, 107, 'subadmin', 2, 'EDIT_REVIEW', 'Reviewer updated review ID #68 on film ID #153', '::1', '2026-11-17 16:12:10'),
(363, 105, 'Vuong1', 2, 'USER_LOGIN', 'User \'Vuong1\' (test@tokusatsu.com) logged in successfully', '::1', '2026-11-17 16:21:31'),
(364, 1, 'Admin', 1, 'DEMOTE_USER', 'Demoted Sub-Admin \'Vuong1\' (ID: 105) to regular Member', '::1', '2026-11-17 16:21:59'),
(365, 105, 'Vuong1', 0, 'USER_LOGIN', 'User \'Vuong1\' (test@tokusatsu.com) logged in successfully', '::1', '2026-11-17 16:22:18'),
(366, 1, 'Admin', 1, 'BLOCK_USER', 'The System Boss blocked user \'Vuong1\' (ID: 105, Role: 0) for 1 hour (until 17/11/2026 17:25) | Reason: test function', '::1', '2026-11-17 16:25:20'),
(367, 1, 'Admin', 1, 'UNBLOCK_USER', 'The System Boss unblocked user account \'Vuong1\' (ID: 105)', '::1', '2026-11-17 16:25:40'),
(369, 105, 'Vuong1', 0, 'BLOCKED_LOGIN_ATTEMPT', 'Blocked user \'Vuong1\' (ID: 105) attempted login while suspended until 18/11/2026 16:25', '::1', '2026-11-17 16:26:00'),
(371, 1, 'Admin', 1, 'PROMOTE_USER', 'Promoted user \'Vuong1\' (ID: 105) to Sub-Admin', '::1', '2026-11-17 16:26:14'),
(373, 105, 'Vuong1', 2, 'USER_LOGIN', 'User \'Vuong1\' (test@tokusatsu.com) logged in successfully', '::1', '2026-11-17 16:26:24'),
(375, 107, 'subadmin', 2, 'BLOCKED_LOGIN_ATTEMPT', 'Blocked user \'subadmin\' (ID: 107) attempted login while suspended until 18/11/2026 16:26', '::1', '2026-11-17 16:26:47'),
(386, 105, 'Vuong1', 2, 'USER_LOGIN', 'User \'Vuong1\' (test@tokusatsu.com) logged in successfully', '::1', '2026-11-17 16:28:30'),
(388, 107, 'subadmin', 2, 'USER_LOGIN', 'User \'subadmin\' (Sub_admin@tokusatsu.com) logged in successfully', '::1', '2026-11-17 16:28:46');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `category`
--

INSERT INTO `category` (`id`, `category_name`, `description`) VALUES
(1, 'Kamen Rider', 'Series about masked motorcyclist superheroes fighting for human freedom'),
(2, 'Super Sentai', 'Color-coded team superhero franchise with giant combining Mecha'),
(3, 'Ultra Series', 'Giant alien beings of light defending Earth from cosmic Kaiju'),
(4, 'Metal Heroes', 'Armored cyborg, space sheriff and metal-clad warriors franchise'),
(5, 'Garo Universe', 'Dark fantasy Tokusatsu chronicles of Makai Knights and Horrors'),
(6, 'Kaiju Movie Franchise', 'Legendary giant monsters and cinematic colossus universe'),
(7, 'Giant Hero', 'Hyper-agent computer superhero fighting digital monsters');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `films`
--

CREATE TABLE `films` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `images` varchar(255) DEFAULT NULL,
  `backdrop_image` varchar(255) DEFAULT NULL,
  `trailer_url` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `tmdb_id` int(11) DEFAULT NULL,
  `release_year` int(11) DEFAULT NULL,
  `runtime` int(11) DEFAULT NULL,
  `is_anniversary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `films`
--

INSERT INTO `films` (`id`, `title`, `description`, `images`, `backdrop_image`, `trailer_url`, `user_id`, `category_id`, `tmdb_id`, `release_year`, `runtime`, `is_anniversary`, `created_at`, `updated_at`, `status`) VALUES
(1, 'No.1 Sentai Gozyuger', 'A new generation of heroes rises as the “Number One” team, fighting powerful enemies to prove their strength and unity.', 'SG.png', NULL, NULL, 1, 2, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:34:26', 'approved'),
(2, 'Kamen Rider OOO', 'A wandering young man gains the power of OOO and battles the Greeed monsters born from human desire while learning the true meaning of greed and selflessness.', 'ooo.png', NULL, NULL, 1, 1, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:28:57', 'approved'),
(3, 'Kamen Rider W', 'Two detectives share one body to become Kamen Rider W, solving mysteries and fighting Dopants in a city filled with hidden crimes.', 'w.png', NULL, NULL, 1, 1, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:30:25', 'approved'),
(4, 'Kamen Rider Dragon Knight', 'A young man enters a parallel dimension and becomes a Rider to fight monsters and uncover the truth behind his missing father.', 'ryuki.png', NULL, NULL, 1, 1, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:30:49', 'approved'),
(5, 'Kamen Rider Ghost', 'After dying, a boy is revived as Ghost and must collect heroic spirits within a time limit to regain his life.', 'ghost.png', NULL, NULL, 1, 1, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:31:29', 'approved'),
(6, 'Kamen Rider Kuuga', 'A kind-hearted man becomes Kuuga to protect humanity from the deadly Gurongi tribe in a grounded and emotional battle.', 'kuuga.png', NULL, NULL, 1, 1, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:31:45', 'approved'),
(7, 'Kamen Rider Kiva', 'A lonely violinist transforms into Kiva, fighting monsters known as Fangire while uncovering secrets tied to his past and his father.', 'kiva.jfif', NULL, NULL, 1, 1, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:31:57', 'approved'),
(8, 'Kamen Rider Kabuto', 'A confident warrior fights shape-shifting aliens called Worms using super speed, walking his own path as the strongest Rider.', 'kabuto.png', NULL, NULL, 1, 1, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:32:09', 'approved'),
(9, 'Kamen Rider Den-O', 'A timid man partners with time-traveling Imagin to protect the timeline from being altered by enemies from the future.', 'den-O.png', NULL, NULL, 1, 1, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:32:22', 'approved'),
(10, 'Kamen Rider Decade', 'A traveler journeys across parallel Rider worlds, risking their destruction as he searches for his true identity.', 'decade.png', NULL, NULL, 1, 1, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:32:33', 'approved'),
(11, 'Kamen Rider Build', 'A genius physicist with lost memories fights in a divided nation, uncovering a conspiracy behind the power of the Riders.', 'build.jpg', NULL, NULL, 1, 1, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 10:45:05', 'approved'),
(12, 'Kamen Rider Wizard', 'A magician Rider protects people from Phantoms creatures born from despair while keeping hope alive in a dark world.', '17.png', NULL, NULL, 1, 1, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:33:05', 'approved'),
(13, 'Kyoryu Sentai Zyuranger', 'Ancient warriors awaken to battle the evil witch Bandora using the power of dinosaurs to save Earth.', 'zyuranger.jfif', NULL, NULL, 1, 2, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:34:35', 'approved'),
(14, 'Mirai Sentai Timeranger', 'Time travelers from the future chase criminals into the past, learning about destiny and changing fate.', 'timeranger.png', NULL, NULL, 1, 2, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:34:44', 'approved'),
(15, 'Samurai Sentai Shinkenger', 'Samurai warriors protect the world from demons emerging from another realm, bound by duty and honor.', '25.png', NULL, NULL, 1, 2, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:34:54', 'approved'),
(16, 'Kaizoku Sentai Gokaiger (2011)', 'Kaizoku Sentai Gokaiger (2011) is a Japanese tokusatsu superhero series celebrating the 35th anniversary of the Super Sentai franchise. The story follows five space pirates led by Captain Marvelous who arrive on Earth in search of the Greatest Treasure in the Universe. When the evil Space Empire Zangyack launches a new invasion, the pirates become the Gokaiger and use Ranger Keys to transform into the legendary warriors of the previous 34 Super Sentai teams. Together, they fight to protect Earth while discovering the true meaning of heroism, friendship, and the legacy of Super Sentai.', 'gokaizer_anni.jpg', NULL, NULL, 1, 2, NULL, NULL, NULL, 1, '2026-03-21 05:44:00', '2026-11-14 10:24:24', 'approved'),
(17, 'Avataro Sentai Donbrothers', 'A chaotic team of heroes battles monsters born from human desires in a story full of humor and emotional twists.', '23.png', NULL, NULL, 1, 2, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:35:19', 'approved'),
(18, 'Ultraman Tiga (1996)', 'Ultraman Tiga (1996) is a Japanese tokusatsu superhero series. The story centers on Daigo Madoka, a member of the Earth-defense organization known as GUTS. He discovers a mysterious, ancient source of power that grants him the ability to transform into Ultraman Tiga. As giant monsters and extraterrestrial threats emerge across the globe, Daigo works alongside GUTS to protect humanity, unravel the secrets of an ancient civilization, and combat dark forces. Through his courage, hope, and the power of light, Ultraman Tiga becomes humanity’s greatest defender.', 'tiga.jpg', NULL, NULL, 1, 3, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 10:24:50', 'approved'),
(19, 'Ultraman Dyna', 'A cheerful hero protects Earth from cosmic threats while exploring the mysteries of space and humanity’s future.', 'dyna.avif', NULL, NULL, 1, 3, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:36:28', 'approved'),
(20, 'Ultraman Gaia', 'Two opposing Ultramen represent Earth and the sea, clashing over how to protect the planet from destruction.', 'gaia.jpg', NULL, NULL, 1, 3, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:36:37', 'approved'),
(21, 'Ultraman Mebius', 'A young Ultraman learns about humanity while fighting alongside a defense team to protect Earth.', 'mebius.webp', NULL, NULL, 1, 3, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:36:47', 'approved'),
(22, 'Ultraman Zero', 'First appeared in Mega Monster Battle: Ultra Galaxy Legends (2009). He is the son of Ultraseven. He has a cool, confident personality, sometimes arrogant but always righteous. He was once exiled for breaking the rules but later redeemed himself and became one of the strongest Ultramen. He uses the Zero Slugger as his weapon and has the ability to travel across different universes. He also serves as a mentor to many Ultramen, including Ultraman Z. In short, he is a veteran hero with top-tier power.\r\n\r\nA rookie Ultraman partners with a human host to battle monsters and grow into a true hero.', 'zero.png', NULL, NULL, 1, 3, NULL, NULL, NULL, 0, '2026-03-21 05:44:00', '2026-11-14 08:36:58', 'approved'),
(75, 'Ultraman Z (Ultraman Zett)', 'Appeared in Ultraman Z (2020). He is a disciple of Ultraman Zero. He has an enthusiastic personality, sometimes a bit clumsy, but very sincere. The story follows his journey alongside a human partner (Haruki), fighting monsters and gradually growing into a true Ultraman. His special feature is using the Z Riser and Medals to transform, with multiple forms such as Alpha Edge and Delta Rise Claw. In short, he is a young warrior in the process of maturing.', 'zett.jpg', NULL, NULL, 1, 3, NULL, NULL, NULL, 0, '2026-04-10 19:09:39', '2026-11-03 08:18:03', 'approved'),
(76, 'Ultraman Geed', 'Appeared in Ultraman Geed (2017). He is the son of the infamous villain Ultraman Belial. He has a kind personality and constantly strives to overcome his dark origins. The story focuses on his struggle to prove that he is not evil despite his lineage. He uses the Fusion Rise system to combine the powers of other Ultramen, with his notable form being Royal Mega Master. In short, he is a character who represents “the son of a villain who chooses to become a hero.”', '16.png', NULL, NULL, 1, 3, NULL, NULL, NULL, 0, '2026-04-10 19:13:43', '2026-04-10 19:59:58', 'approved'),
(77, 'Mahou Sentai Magiranger', 'Appeared in Mahou Sentai Magiranger (2005). The story follows a family of five siblings who inherit magical powers from their mother, a legendary sorceress. Each member becomes a Magiranger, using elemental magic to fight against the forces of darkness from the Infershia realm. Throughout the series, they not only battle powerful enemies but also grow stronger through the bonds of family and courage. The team uses magical transformation devices and can combine their powers into stronger forms. In short, it is a story about family, magic, and unity in the fight against evil.', '18.png', NULL, NULL, 1, 2, NULL, NULL, NULL, 0, '2026-04-10 19:19:32', '2026-11-14 08:34:16', 'approved'),
(78, 'Kishiryu Sentai Ryusoulger', 'A team of noble knights harnesses the power of ancient dinosaurs to protect Earth from the evil Druidon tribe. Using mystical Ryusoul items and powerful Kishiryu mecha, they fight to preserve peace while upholding the code of chivalry and courage.', '20.png', NULL, NULL, 1, 2, NULL, NULL, NULL, 0, '2026-04-10 19:35:28', '2026-11-14 08:34:02', 'approved'),
(79, 'Tokusou Sentai Dekaranger (S.P.D)', 'A special police team known as S.P.D. (Special Police Dekaranger) protects Earth from alien criminals. Each member is trained to investigate, track, and eliminate threats from space, using advanced technology, teamwork, and a strong sense of justice to maintain peace across the galaxy.', '22.png', NULL, NULL, 1, 2, NULL, NULL, NULL, 0, '2026-04-10 19:45:28', '2026-11-14 08:33:46', 'approved'),
(80, 'Power Rangers Jungle Fury', 'A group of skilled martial artists trains under the ancient Pai Zhua order to master the power of animal spirits. When the evil Dai Shi is unleashed, they become Jungle Fury Rangers and use their abilities to protect the world from darkness. Through intense training, discipline, and teamwork, they unlock new powers and forms while facing powerful enemies and learning the true meaning of strength and balance.', '24.png', NULL, NULL, 1, 2, NULL, NULL, NULL, 0, '2026-04-10 20:04:35', '2026-11-14 08:33:23', 'approved'),
(90, 'Ultraman Cosmos', 'The story follows Musashi Haruno, a kind-hearted young man who encounters Ultraman Cosmos, a giant being of light who values peace over destruction. After forming a bond with Cosmos, Musashi becomes his human partner and works with the defense team EYES (Elite Young Experts Squad).\r\n\r\nTogether, they face various alien creatures and monsters, but instead of simply defeating them, Cosmos often tries to calm, heal, or relocate these beings. Throughout the series, Musashi learns important lessons about coexistence, empathy, and protecting life in all forms, while confronting threats that challenge his ideals of peace.', '26.png', NULL, NULL, 1, 3, NULL, NULL, NULL, 0, '2026-04-14 23:56:21', '2026-11-03 05:50:05', 'approved'),
(91, 'Space Sheriff Gavan (1982)', 'Space Sheriff Gavan (1982) is a pioneering tokusatsu series produced by Toei that follows Retsu Ichijouji, an intergalactic warrior defending Earth from the Makuu Empire using high tech armor and weapons.', 'gavan.jpg', NULL, NULL, 1, 4, NULL, NULL, NULL, 0, '2026-04-17 00:00:00', '2026-11-14 10:26:14', 'approved'),
(92, 'GARO: Makai Senki (2011)', 'The story follows Kouga Saejima, who is promoted to a personal Makai Knight of the Senate after vanquishing powerful Horrors. However, Kouga and his rival Rei Suzumura (the Silver Knight Zero) find themselves afflicted with the dangerous Seal of Destruction, which marks them for an early death.\r\n\r\nAs Kouga continues his relentless mission to hunt Horrors while trying to find a way to break his curse, he teams up with a talented young Makai Priest named Leo Fudō. Alongside these battles, the series explores the deeper political tensions within the Makai community, a conflict involving Leo\'s estranged twin brother Sigma who harbors a dark plot against the Senate and Makai Knights, and the evolving relationship between Kouga and Kaoru as she pursues her career as an illustrator.', 'Garo.jpg', NULL, NULL, 1, 5, NULL, NULL, NULL, 0, '2026-04-17 00:00:00', '2026-11-14 10:26:59', 'approved'),
(93, 'Godzilla Minus Zero', 'Godzilla Minus Zero is a historical survival drama set in post World War Two Japan where the devastated nation faces absolute despair and destruction when a terrifying monster attacks without any modern military support.', 'godzilla.jpg', NULL, NULL, 1, 6, NULL, NULL, NULL, 0, '2026-04-17 00:00:00', '2026-11-14 10:28:00', 'approved'),
(94, 'Gridman Universe (2023)', 'The story takes place after the peaceful resolution of the events in both series, where Yuta Hibiki, Rikka Takarada, Sho Utsumi, and the students from Tsutsujidai High School are enjoying their ordinary daily lives. However, peace is abruptly shattered when mysterious anomalies begin to warp reality, bringing characters from different dimensions together including Yomogi Asanaka, Yume Minami, Koyomi Yamanaka, and Chise Asukagawa from the Dynazenon world. As a new cosmic threat emerges to endanger all of existence, Gridman returns once more, requiring the heroes from both universes to unite, combine their powers, and fight to protect their bonds and shared future.', 'gridman.jpg', NULL, NULL, 1, 7, NULL, NULL, NULL, 0, '2026-04-17 00:00:00', '2026-11-14 10:28:38', 'approved'),
(98, 'Garo: Taiga', 'The 20th anniversary film of the Garo tokusatsu franchise by Tohokushinsha, written and directed by Keita Amemiya and starring Shoichiro Kitada, is a prequel exploring the youth of Saejima Taiga who has just inherited the Golden Knight Garo armor. Alongside a Makai Guide named Fuki, Taiga embarks on a mission to pursue the villain Jado and reclaim a sacred compass holding the souls of the Five Holy Beasts, marking his journey of growth and the resolute will of a legendary knight.', 'garo_taiga.jpg', NULL, NULL, 1, 5, NULL, NULL, NULL, 1, '2026-11-14 07:19:24', '2026-11-14 10:29:06', 'approved'),
(99, 'Shin Godzilla (2016)', 'Shin Godzilla (2016) is a sci-fi political thriller set in modern Japan that focuses on the bureaucratic and governmental crisis triggered by the sudden appearance and continuous evolution of a colossal mutated monster.', 'shin_godzilla.webp', NULL, NULL, 1, 6, NULL, NULL, NULL, 0, '2026-11-14 07:37:19', '2026-11-14 10:29:31', 'approved'),
(100, 'Space Sheriff Shaider: NEXT GENERATION (2014)', 'Space Sheriff Shaider: NEXT GENERATION (2014) is a tokusatsu V-Cinema film directed by Koichi Sakamoto and written by Naruhisa Arakawa. The story follows Shu Karasuma, the new Shaider, as he is assigned a mission to rescue a kidnapped scientist\'s daughter while dealing with friction alongside his partner Tamy, who is unhappy with his womanizing habits.', 'shaider_anni.jpg', NULL, NULL, 1, 4, NULL, NULL, NULL, 1, '2026-11-14 07:53:02', '2026-11-14 10:30:05', 'approved'),
(101, 'Space Sheriff Shaider (1984)', 'Space Sheriff Shaider (1984) is a Japanese tokusatsu sci-fi series about a heroic space sheriff named Shaider who protects Earth from the evil Fuuma Empire. Armed with advanced technology, powerful weapons, and courage, he battles dangerous enemies to defend humanity and maintain peace across the galaxy.', 'shaider.jpg', NULL, NULL, 1, 4, NULL, NULL, NULL, 0, '2026-11-14 08:13:26', '2026-11-14 10:30:27', 'approved'),
(102, 'Space Sheriff Sharivan (1983)', 'Space Sheriff Sharivan (1983) is a Japanese tokusatsu sci-fi series about a brave space sheriff named Sharivan who protects Earth from the evil Madou Empire. Equipped with powerful weapons, advanced technology, and extraordinary fighting skills, he battles deadly enemies to defend humanity and restore peace throughout the universe.', 'sharivan.jpg', NULL, NULL, 1, 4, NULL, NULL, NULL, 0, '2026-11-14 08:14:43', '2026-11-14 10:30:56', 'approved'),
(103, 'Megabeast Investigator Juspion (1985)', 'Megalobeast Investigator Juspion (1985) is a Japanese tokusatsu sci-fi series about a heroic space warrior named Juspion who battles the evil Satan Goss and his monstrous forces threatening the universe. Armed with advanced weapons, powerful armor, and his giant robot Daileon, Juspion fights to protect Earth and fulfill his destiny as a legendary warrior of justice.', 'juspion.webp', NULL, NULL, 1, 4, NULL, NULL, NULL, 0, '2026-11-14 08:18:31', '2026-11-14 10:31:35', 'approved'),
(104, 'Sekai Ninja Sen Jiraiya (1988)', 'Sekai Ninja Sen Jiraiya (1988) is a Japanese tokusatsu series about a young ninja named Jiraiya who protects the world from the evil Youkai Clan and other powerful enemies seeking the legendary Pako treasure. Trained in ancient ninja techniques and equipped with advanced weapons and armor, Jiraiya fights alongside his allies to defend humanity and preserve peace.', 'Jiraiya.jpg', NULL, NULL, 1, 4, NULL, NULL, NULL, 0, '2026-11-14 08:23:52', '2026-11-14 10:31:59', 'approved'),
(105, 'Kamen Rider OOO 10th: The Core Medals of Resurrection (2022)', 'Kamen Rider OOO 10th: The Core Medals of Resurrection (2022) is a Japanese tokusatsu superhero film celebrating the tenth anniversary of Kamen Rider OOO. Set ten years after the original series, the story follows Eiji Hino as he returns to Japan amid a devastating crisis caused by the resurrected ancient OOO and the revived Greeed. As humanity faces extinction, Eiji reunites with Ankh and fights to protect the world while confronting the ultimate fate of their friendship.', 'ooo_anni.jpg', NULL, NULL, 1, 1, NULL, NULL, NULL, 1, '2026-11-14 08:28:38', '2026-11-14 10:32:36', 'approved'),
(106, 'Kamen Rider Geats: 4 Aces and the Black Fox (2023)', 'Kamen Rider Geats: 4 Aces and the Black Fox (2023) is a Japanese tokusatsu superhero film featuring Ace Ukiyo and the other Desire Grand Prix Riders. When a mysterious giant tree appears in Tokyo, Ace is suddenly divided into four different versions of himself, each representing a distinct personality. As a powerful enemy known as the Black Fox emerges, the four Aces must overcome their differences and unite their abilities to protect the world from a devastating threat.', 'geats.jpg', NULL, NULL, 1, 1, NULL, NULL, NULL, 0, '2026-11-14 08:45:52', '2026-11-14 10:33:31', 'approved'),
(107, 'Kamen Rider Gotchard (2023)', 'Kamen Rider Gotchard (2023) is a Japanese tokusatsu superhero series about a high school student named Houtaro Ichinose who becomes Kamen Rider Gotchard through the power of alchemy. After 101 artificial lifeforms known as Chemies escape from their sealed cards, Houtaro joins forces with fellow alchemists to recover them and prevent evil forces from transforming them into dangerous monsters. Along the way, he discovers the true meaning of friendship, courage, and becoming a great alchemist.', 'gotchard.jpg', NULL, NULL, 1, 1, NULL, NULL, NULL, 0, '2026-11-14 08:49:41', '2026-11-14 10:33:58', 'approved'),
(108, 'Kamen Rider Drive (2014)', 'Kamen Rider Drive (2014) is a Japanese tokusatsu superhero series about a detective named Shinnosuke Tomari who becomes Kamen Rider Drive to protect humanity from the Roidmudes, mysterious mechanical lifeforms seeking to dominate the world. Armed with advanced technology, powerful transformation abilities, and his supercar Tridoron, Shinnosuke investigates extraordinary crimes while uncovering the secrets behind the Global Freeze and fighting to preserve peace between humans and artificial lifeforms.', 'drive.jpg', NULL, NULL, 1, 1, NULL, NULL, NULL, 0, '2026-11-14 08:52:02', '2026-11-14 10:34:23', 'approved'),
(109, 'Ultraman Tiga 30th Premium Stage (2026)', 'Ultraman Tiga 30th Premium Stage (2026) is a special anniversary event celebrating the 30th anniversary of Ultraman Tiga. Featuring original cast members including Hiroshi Nagano, Takami Yoshimoto, Mio Takaki, and Akitoshi Otaki, the event brings together the legendary GUTS team for a memorable reunion. Through exclusive talk sessions, special video screenings, and nostalgic behind the scenes stories, the performers revisit unforgettable moments from the original series and share their lasting memories with fans. Celebrating the enduring legacy of Ultraman Tiga, the event delivers a heartfelt message of hope, courage, and the power of light to a new generation of fans.', 'tiga_anni.jpg', NULL, NULL, 1, 3, NULL, NULL, NULL, 1, '2026-11-14 09:01:44', '2026-11-14 10:34:55', 'approved'),
(110, 'Ultraman Saga (2012) - 45th Anniversary Crossover Movie', 'Ultraman Saga (2012) is a Japanese tokusatsu superhero movie celebrating the 45th anniversary of the Ultraman franchise. Set fifteen years after Shin Asuka disappeared into a black hole, the story follows a young fighter pilot named Taiga Nozomu who becomes one with Ultraman Zero. When the evil Alien Bat invades an alternate Earth and threatens the last surviving children, Taiga joins forces with Ultraman Dyna, Ultraman Cosmos, and the brave warriors of Team U. As the battle against the terrifying Hyper Zetton reaches its climax, the three Ultra warriors unite their powers to become Ultraman Saga, humanity\'s ultimate hope for survival.', 'saga.jpg', NULL, NULL, 1, 3, NULL, NULL, NULL, 1, '2026-11-14 09:09:16', '2026-11-14 10:35:42', 'approved'),
(111, 'Ultraman Orb The Movie: Let Me Borrow the Power of Bonds! (2017)', 'Ultraman Orb The Movie: Let Me Borrow the Power of Bonds! (2017) is a Japanese tokusatsu superhero movie featuring Gai Kurenai and Ultraman Orb. When the mysterious space witch Murnau captures Ultraman X, Ultraman Ginga, and Ultraman Victory to turn them into precious jewels, Gai joins forces with the SSP and Daichi Oozora to rescue the legendary Ultra Warriors. As a powerful threat emerges to destroy Earth, Orb harnesses the strength of friendship and the bonds between the New Generation Heroes to unlock his ultimate form, Orb Trinity, and fight for the future of humanity.', 'orb.jpg', NULL, NULL, 1, 3, NULL, NULL, NULL, 0, '2026-11-14 09:13:51', '2026-11-14 10:36:30', 'approved'),
(112, 'Ultraman X The Movie: Here He Comes! Our Ultraman (2016)', 'Ultraman X The Movie: Here He Comes! Our Ultraman (2016) is a Japanese tokusatsu superhero movie featuring Daichi Ozora and Ultraman X. When a mysterious treasure hunter accidentally breaks an ancient seal, the terrifying monster Zaigorg is unleashed upon Earth. As Xio struggles against a powerful army of monsters, Daichi and Ultraman X join forces with Ultraman Tiga, the Original Ultraman, and other legendary Ultra Warriors to protect humanity. Through the power of unity and courage, Ultraman X unlocks the ultimate Beta Spark Armor to face the ultimate evil and save the world.', 'x.jpg', NULL, NULL, 1, 3, NULL, NULL, NULL, 0, '2026-11-14 09:16:20', '2026-11-14 10:36:55', 'approved'),
(113, 'Denkou Choujin Gridman (1993)', 'Gridman the Hyper Agent (1993) is a Japanese tokusatsu superhero series about three computer savvy teenagers named Naoto Sho, Yuka Inoue, and Ippei Baba who discover a mysterious interdimensional police officer named Gridman inside their homemade computer. When the evil computer demon Kahn Digifer begins creating digital monsters to invade the real world, Gridman merges with Naoto to battle these dangerous threats and protect humanity. Together with his friends, Gridman fights to defend both the Computer World and the Human World from destruction.', 'denkou.jpg', NULL, NULL, 1, 7, NULL, NULL, NULL, 0, '2026-11-14 09:19:46', '2026-11-14 10:37:53', 'approved'),
(114, 'Rebirth of Mothra III (1998)', 'Rebirth of Mothra III (1998) is a Japanese tokusatsu kaiju movie featuring Mothra Leo and the terrifying King Ghidorah. When the legendary three headed space dragon returns to Earth and begins abducting children to feed on their life energy, Mothra Leo rises to protect humanity. However, Ghidorah proves too powerful to defeat in the present, forcing Mothra to travel back to the Cretaceous Period to confront the younger monster. Through extraordinary transformations and the power of courage and friendship, Mothra must overcome the ultimate enemy and save Earth from destruction.', 'mothra.webp', NULL, NULL, 1, 6, NULL, NULL, NULL, 0, '2026-11-14 09:29:17', '2026-11-14 10:38:34', 'approved'),
(151, 'testpostfilm', 'test post file. abc', '1794904637_b5a508a10e37eb0a.jpg', NULL, NULL, 106, 5, NULL, NULL, NULL, 0, '2026-11-17 08:37:17', '2026-11-17 08:37:17', 'pending'),
(153, 'Ultraman Decker D', 'Set years after the events of Ultraman Trigger, peace on Earth is shattered when a mysterious swarm of alien lifeforms known as the Sphere suddenly attacks and isolates the planet from space. Amid the chaos, a young and determined rice cracker vendor named Kanata Asumi steps up to protect others, bonding with a new light to transform into Ultraman Decker. Joining the newly reformed GUTS-Select defense team, Kanata fights alongside his comrades to defend humanity and uncover the truth behind the Sphere\'s invasion.', 'DeckerD.jpg', NULL, NULL, 106, 3, NULL, NULL, NULL, 0, '2026-11-17 08:43:43', '2026-11-17 08:58:42', 'approved');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_message` text NOT NULL,
  `admin_reply` text DEFAULT NULL,
  `status` enum('pending','seen','replied') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `user_message`, `admin_reply`, `status`, `created_at`, `updated_at`) VALUES
(29, 105, 'Nice to meet you! I have a bit requirement for you.', 'hello', 'replied', '2026-11-17 08:16:59', '2026-11-17 08:17:34'),
(30, 106, 'I have a pretty good movie that I’d like to post for everyone to enjoy. I look forward to its approval!', 'Thank you for your contribution.', 'replied', '2026-11-17 08:47:17', '2026-11-17 08:47:43');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `film_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `headline` varchar(150) DEFAULT NULL,
  `rating` tinyint(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `recommend` tinyint(1) NOT NULL DEFAULT 1,
  `is_spoiler` tinyint(1) NOT NULL DEFAULT 0,
  `tags` varchar(255) DEFAULT NULL,
  `ticket_proof` varchar(255) DEFAULT NULL,
  `is_verified_watcher` tinyint(1) NOT NULL DEFAULT 0,
  `helpful_count` int(11) NOT NULL DEFAULT 0,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `reviews`
--

INSERT INTO `reviews` (`id`, `film_id`, `user_id`, `headline`, `rating`, `recommend`, `is_spoiler`, `tags`, `ticket_proof`, `is_verified_watcher`, `helpful_count`, `content`, `created_at`) VALUES
(64, 108, 105, 'Happy, happy', 4, 1, 0, '#EmotionalStory, #GreatSoundtrack', NULL, 0, 1, 'This is a great movie. I love it and want to recommend it to everyone who likes it too.', '2026-11-17 08:09:40'),
(67, 108, 106, 'Thrilling, gripping, and engaging', 5, 1, 1, '#EmotionalStory, #GreatSoundtrack', NULL, 0, 0, 'I was genuinely surprised by this movie. Seeing Kamen Rider Dark Drive appear and realizing the truth behind the Next System\'s betrayal kept me on the edge of my seat.', '2026-11-17 08:22:31');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `review_votes`
--

CREATE TABLE `review_votes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `is_helpful` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `review_votes`
--

INSERT INTO `review_votes` (`id`, `user_id`, `review_id`, `is_helpful`, `created_at`) VALUES
(14, 106, 64, 1, '2026-11-17 15:30:35');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` tinyint(1) DEFAULT 0 COMMENT '0 = User, 1 = Boss, 2+ = Sub-Admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `avatar` varchar(255) DEFAULT 'default_avatar.webp',
  `bio` text DEFAULT NULL,
  `favorite_genre` varchar(100) DEFAULT NULL,
  `status` enum('active','on_break') DEFAULT 'active',
  `credibility_score` int(11) DEFAULT 0,
  `badge` varchar(50) DEFAULT 'Newbie',
  `is_deleted` tinyint(1) DEFAULT 0,
  `is_blocked` tinyint(1) NOT NULL DEFAULT 0,
  `blocked_until` datetime DEFAULT NULL,
  `block_reason` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`, `avatar`, `bio`, `favorite_genre`, `status`, `credibility_score`, `badge`, `is_deleted`, `is_blocked`, `blocked_until`, `block_reason`, `reset_token`, `reset_expires`) VALUES
(1, 'Admin', 'admin@tokusatsu.com', '$2y$12$6tPWdQ.6.lbkSYARWcFdyOA2BTkCAa4eWhQC6Sb4LnuKOyPSpEsn.', 1, '2026-03-12 05:00:00', '2026-11-15 10:39:13', 'admin_ava.jpeg', '', NULL, 'active', 9999, 'System Boss', 0, 0, NULL, NULL, NULL, NULL),
(105, 'Vuong1', 'test@tokusatsu.com', '$2y$12$9lTDZ73lDqkLGGBNi0ObfuxdN857F04FFDnjkqf3RfnlLqUE1NNU6', 0, '2026-11-17 08:06:37', '2026-11-17 09:28:37', '1794903352_6bf37d12dc7ce67c.jpg', 'I love this one.', '1', 'active', 0, 'Member', 0, 0, NULL, NULL, NULL, NULL),
(106, 'Amory', 'amory@tokusatsu.com', '$2y$12$nEBJyjjaHpCTkmWShYUpZuEwxNVimXfUbnLRfrtMujvVBSiHKzELW', 0, '2026-11-17 08:19:08', '2026-11-17 08:21:26', 'default_avatar.webp', 'abc', '2', 'active', 0, 'Member', 0, 0, NULL, NULL, NULL, NULL),
(107, 'subadmin', 'Sub_admin@tokusatsu.com', '$2y$12$p2y5gJAVfXp3FokxM0rpkuhO6/BeX8LoU37RaSJefbsZYCVydiPKG', 2, '2026-11-17 09:03:34', '2026-11-17 09:29:02', 'default_avatar.webp', NULL, NULL, 'active', 0, 'Sub-Admin', 0, 0, NULL, NULL, NULL, NULL);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`);

--
-- Chỉ mục cho bảng `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `films`
--
ALTER TABLE `films`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Chỉ mục cho bảng `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `film_id` (`film_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_film_helpful` (`film_id`,`helpful_count` DESC);

--
-- Chỉ mục cho bảng `review_votes`
--
ALTER TABLE `review_votes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_review_vote` (`user_id`,`review_id`),
  ADD KEY `idx_review_vote` (`review_id`),
  ADD KEY `idx_user_vote` (`user_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=392;

--
-- AUTO_INCREMENT cho bảng `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `films`
--
ALTER TABLE `films`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- AUTO_INCREMENT cho bảng `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT cho bảng `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT cho bảng `review_votes`
--
ALTER TABLE `review_votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- Ràng buộc đối với các bảng kết xuất
--

--
-- Ràng buộc cho bảng `films`
--
ALTER TABLE `films`
  ADD CONSTRAINT `films_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `films_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ràng buộc cho bảng `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`film_id`) REFERENCES `films` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
